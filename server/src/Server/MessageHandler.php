<?php

namespace Game\Server;

use Game\Db\Database;
use Game\Db\GameState;
use Game\Game\GameFactory;
use Game\Game\GameInterface;
use Game\Library\Lobby;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;
use Game\Server\Messages\GameMessage;
use Game\Server\Messages\StatusMessage;

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
        echo "\n !!!!!!HERE Disconnect ";
        if (!$this->clientHandler->clientExists($client)) {
            echo "\n !!!!!!HERE Client does not exist";
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


    private function createMessage(string $status): string
    {
        return (new StatusMessage($status))->getMessage();
    }

    private function createMessageWithGameInfo(string $status, GameInterface $game, $playerId): string
    {
        return (new StatusMessage($status))->addTo(new GameMessage($game, $playerId))->getMessage();
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
                    $this->sendMessage(
                        $this->clientHandler->getClientByHash($clientHash),
                        $this->createMessageWithGameInfo("playerLeft", $game, $playerId)
                    );
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
        var_dump($msg);

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
        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        if (!$this->checkValidGameRequest($gameId, $client, $playerId)) {
            return;
        }
        $game = $this->games[$gameId];

        $this->sendMessage(
            $client,
            $this->createMessageWithGameInfo("inGame", $game, $playerId)
        );

        if (!$reconnect) {
            return;
        }

        // notify other player
        $players = $game->getPlayers();
        foreach ($players as $player) {
            if ($playerId === $player) {
                continue;
            }
            $client = $this->clientHandler->getClientByPlayerId($player);
            if (is_null($client)) {
                return;
            }
            $this->sendMessage(
                $client,
                $this->createMessageWithGameInfo("playerRejoin", $game, $player)
            );
        }
    }

    private function gameExists($gameId)
    {
        return array_key_exists($gameId, $this->games);
    }

    private function checkValidGameRequest($gameId, $client, $playerId)
    {
        $message = $this->createMessage("invalidGame");
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

        $gameOver = $game->gameOver();
        $status = ($gameOver) ?  "gameOver" : "inGame";

        $this->sendMessage(
            $client1,
            $this->createMessageWithGameInfo($status, $game, $playerId1)
        );

        if ($this->clientHandler->playerIsConnected($playerId2)) {

            $client2 = $this->clientHandler->getClientByPlayerId($playerId2);

            if (is_null($client2)) {
                return;
            }

            $this->sendMessage(
                $client2,
                $this->createMessageWithGameInfo($status, $game, $playerId2)
            );
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

        $client1 = $this->clientHandler->getClientByHash($client1Hash);
        $client2 = $this->clientHandler->getClientByHash($client2Hash);
        $this->createGame($gameType, $client1, $client2);
    }

    private function notifyPlayerTheyAreInLobby(ConnectionInterface $client)
    {
        $this->sendMessage($client, $this->createMessage("inLobby"));
    }

    private function createGame($gameType, $client1, $client2): void
    {
        $gameId = Uuid::v4();


        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);

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
            $status = "inGame";
            $this->sendMessage(
                $client1,
                $this->createMessageWithGameInfo($status, $game, $playerId1)
            );

            $this->sendMessage(
                $client2,
                $this->createMessageWithGameInfo($status, $game, $playerId2)
            );
        };
    }
}
