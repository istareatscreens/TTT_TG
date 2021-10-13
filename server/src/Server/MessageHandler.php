<?php

namespace Game\Server;

use Game\Db\Database;
use Game\Db\GameState;
use Game\GameFactory;
use Game\Library\Lobby;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class MessageHandler
{
    private static $messageTypes = [
        "makeMove" => Null,
        "joinGame" => Null,
        "joinLobby" => Null
    ];
    private ClientHandler $clientHandler;
    private array $games;
    private array $lobbies;
    private GameState $gameState;
    private GameFactory $gameFactory;

    public function __construct(
        ClientHandler $clientHandler,
        GameFactory $gameFactory,
        Database $db
    ) {
        $this->clientHandler = $clientHandler;
        $this->lobbies = array();
        $this->gameState = new GameState($db);
        $this->games = array();
        $this->gameFactory = $gameFactory;
    }

    public function addClient(ConnectionInterface $client, $playerId, SocketServer $socketServer): bool
    {
        return $this->clientHandler->addClient($client, $playerId, $socketServer);
    }

    public function registerClient(
        ConnectionInterface $client,
        string $playerId
    ): bool {
        return $this->clientHandler->validateClient($client, $playerId);
    }

    public function disconnectClient(ConnectionInterface $client): void
    {
        if (!$this->clientHandler->clientExists($client)) {
            return;
        }

        // Handle case where player just joined and quit
        if (!$this->clientHandler->clientHasPlayerId($client)) {
            $this->clientHandler->removeClient($client);
        }

        // Handle case where player messaged the server
        $this->removeFromLobbies($client);
        $playerId = $this->clientHandler->getPlayerIdByClient($client);

        $this->clientHandler->removeClient($client);
        $this->handleGamesAfterDisconnect($playerId);
    }

    private function removeFromLobbies(ConnectionInterface $client): void
    {
        $hash = $this->clientHandler->getClientHash($client);
        foreach ($this->lobbies as &$lobby) {
            $lobby->remove($hash);
        }
    }

    private function handleGamesAfterDisconnect($playerIdDisconnect): void
    {
        $games = $this->gameState->getAllGameIdsPlayerIdsAndClientHashesFromPlayerID(
            $playerIdDisconnect
        );

        if (is_null($games) || count($games) === 0) {
            return;
        }

        $clientAndMessage = [];
        foreach (array_keys($games) as $gameId) {
            foreach ($games[$gameId] as $gameData) {
                [$playerId, $clientHash] = $gameData;

                if ($playerId === $playerIdDisconnect) {
                    continue;
                }

                // Delete game if all players left
                if (is_null($clientHash)) {
                    $this->deleteGame($gameId);
                } else {
                    //notify other user of player leaving 
                    $game = $this->games[$gameId];
                    $state = $game->getState();
                    $winner = $game->getWinner();
                    $gameOverState = $game->getWinningState();
                    $playerNumber = $game->getPlayerNumber($playerId);
                    $message = new MessageOut($gameId);
                    array_push($clientAndMessage, [
                        $this->clientHandler->getClientByHash($clientHash),
                        $message->createMessage(
                            "playerLeft",
                            $playerNumber,
                            $state,
                            $winner,
                            $gameOverState
                        )
                    ]);
                }
            }
        }

        // notify players of disconnect
        foreach ($clientAndMessage as [$client, $message]) {
            $this->sendMessage($client, $message);
        }
    }


    private function sendMessage(ConnectionInterface $client, string $message): void
    {
        echo "\n Client: " . $client->resourceId .  " Message Sent: " . $message . " \n";
        $client->send($message);
    }

    private function validMessage($msg): bool
    {
        return (property_exists($msg, "game") &&
            property_exists($msg, "type") &&
            property_exists($msg, "gameId") &&
            property_exists($msg, "position")) &&
            $this->gameFactory->isValidGame($msg->game) &&
            key_exists($msg->type, MessageHandler::$messageTypes) &&
            strlen($msg->gameId) < 37 &&
            ((is_null($msg->position) && $msg->type !== "makeMove") ||
                $this->gameFactory->isValidPositionInGame(
                    $msg->game,
                    $msg->position
                ));
    }

    public function handleMessage(ConnectionInterface $client, $msg, string $playerId): void
    {

        if (!$this->validMessage($msg)) {
            return;
        }

        $gameType = $msg->game;
        $type = $msg->type;
        $gameId = $msg->gameId;
        $position = $msg->position;
        $reconnect = !$this->clientHandler->clientExists($client);

        if (!$this->registerClient($client, $playerId)) {
            return;
        }

        switch ($type) {
            case "joinLobby":
                $this->addToLobby($gameType, $client);
                $this->matchPlayer($gameType, $client);
                return;
            case "makeMove":
                $this->handleMove($gameId, $client, $position);
                return;
            case "joinGame":
                $this->joinGame($gameId, $client, $reconnect);
                return;
        }
    }

    private function joinGame($gameId, $client, $reconnect): void
    {
        echo "\nIN JOIN GAME: " . $gameId . " reconnect " . $reconnect . "\n";
        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        if (!$this->checkValidGameRequest($gameId, $client, $playerId)) {
            return;
        }
        $game = $this->games[$gameId];

        echo "\nIS valid game!!";
        $message = new MessageOut($gameId);
        $message = $message->createMessage(
            "inGame",
            $game->getPlayerNumber($playerId),
            $game->getState(),
            $game->getWinner(),
            $game->getWinningState()
        );

        $this->sendMessage($client, $message);

        if (!$reconnect) {
            return;
        }

        // notify other player
        $players = $game->getPlayers();
        echo "Players in game: \n";
        print_r($players);
        echo "\n";
        foreach ($players as $player) {
            echo "\nLoop player\n";
            if ($playerId === $player) {
                echo "\nsame as user player\n";
                continue;
            }
            $client = $this->clientHandler->getClientByPlayerId($player);
            if (is_null($client)) {
                echo "\nclient doesnt exist\n";
                return;
            }
            echo "\nSending message\n";
            $message = new MessageOut($gameId);
            $message = $message->createMessage(
                "playerRejoin",
                $game->getPlayerNumber($player),
                $game->getState(),
                $game->getWinner(),
                $game->getWinningState()
            );
            $this->sendMessage($client, $message);
        }
    }

    private function gameExists($gameId)
    {
        return array_key_exists($gameId, $this->games);
    }

    private function checkValidGameRequest($gameId, $client, $playerId)
    {
        $message = (new MessageOut())->createMessage("invalidGame");
        if (!$this->gameExists($gameId)) {
            $this->sendMessage($client, $message);
            return false;
        }

        $game = $this->games[$gameId];
        if (!$game->isPlayer($playerId)) {
            $this->sendMessage($client, $message);
            return false;
        }
        return true;
    }

    private function handleMove($gameId, $client1, $position)
    {
        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        if (!$this->checkValidGameRequest($gameId, $client1, $playerId1)) {
            return;
        }
        $game = $this->games[$gameId];

        $playerIds = $game->getPlayers();
        $playerId2 = ($playerId1 === $playerIds[0]) ? $playerIds[1] : $playerIds[0];

        if (!$game->makeMove($playerId1, $position)) {
            return;
        }

        $winner = $game->getWinner();
        $gameOver = $game->gameOver();
        $status = ($gameOver) ?  "gameOver" : "inGame";
        $state = $game->getState();
        $gameOverState = $game->getWinningState();

        $message = new MessageOut($gameId);

        $message = $message->createMessage(
            $status,
            $game->getPlayerNumber($playerId1),
            $state,
            $winner,
            $gameOverState
        );

        $this->sendMessage($client1, $message);

        if ($this->clientHandler->playerIsConnected($playerId2)) {
            $client2 = $this->clientHandler->getClientByPlayerId($playerId2);
            if (is_null($client2)) {
                return;
            }
            $message = new MessageOut($gameId);
            $message = $message->createMessage(
                $status,
                $game->getPlayerNumber($playerId2),
                $game->getState(),
                $winner,
                $gameOverState
            );
            $this->sendMessage($client2, $message);
        }

        if ($gameOver) {
            $this->deleteGame($gameId);
        }
    }

    private function deleteGame($gameId): void
    {
        $this->gameState->deleteGame($gameId);
        unset($this->games[$gameId]);
    }

    private function addToLobby($gameType, $client): void
    {
        $hash = $this->clientHandler->getClientHash($client);
        if (!key_exists($gameType, $this->lobbies)) {
            $this->createLobby($gameType);
        }
        $lobby = &$this->lobbies[$gameType];
        $lobby->queue($hash);
    }

    private function createLobby($gameType): void
    {
        $this->lobbies[$gameType] = new Lobby();
    }

    private function matchPlayer(
        string $gameType,
        ConnectionInterface $client
    ): void {

        $lobby = &$this->lobbies[$gameType];
        if (!$lobby->isEmpty() && $lobby->size() % 2 !== 0) {
            $this->notifyPlayerTheyAreInLobby($client);
            return;
        }
        $client1Hash = $lobby->shift();
        $client2Hash = $lobby->shift();

        echo "\n JOINING GAME WITH Hash1" . $client1Hash . " hash2 " . $client2Hash . "\n";
        $client1 = $this->clientHandler->getClientByHash($client1Hash);
        $client2 = $this->clientHandler->getClientByHash($client2Hash);
        $this->createGame($gameType, $client1, $client2);
    }

    private function notifyPlayerTheyAreInLobby(ConnectionInterface $client)
    {
        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        $message = new MessageOut();
        $message = $message->createMessage("inLobby");
        echo "\n SENT: " . $message;
        $this->sendMessage($client, $message);
    }

    private function createGame($gameType, $client1, $client2): void
    {
        $gameId = Uuid::v4();


        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);
        echo "\nIN CREATE GAME" . "P1: " . $playerId1 . "P2:" . $playerId2 . "\n";

        $game = $this->gameFactory->createGame(
            $gameType,
            $gameId,
            $playerId1,
            $playerId2
        );

        if ($game && $this->gameState->createGame(
            $game->getId(),
            $playerId1,
            $playerId2
        )) {
            $this->games[$game->getId()] = $game;

            $message = new MessageOut($game->getId());
            $playerNumber1 = $game->getPlayerNumber($playerId1);
            $playerNumber2 = $game->getPlayerNumber($playerId2);
            $message = $message->createMessage("inGame", $playerNumber1);
            $this->sendMessage($client1, $message);

            $message = new MessageOut($game->getId());
            $message = $message->createMessage("inGame", $playerNumber2);
            $this->sendMessage($client2, $message);
        };
    }
}
