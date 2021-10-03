<?php

namespace Game\Server;

use Game\Db\Database;
use Game\Db\GameState;
use Game\GameFactory;
use Game\Library\Lobby;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

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

    public function addClient(ConnectionInterface $client)
    {
        $this->clientHandler->addClient($client);
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
                    $state = $this->games[$gameId]->getState();
                    $winner = $this->games[$gameId]->getWinner();
                    $playerNumber = $this->games[$gameId]->getPlayerNumber($playerId);
                    $message = new MessageOut($playerId, $gameId);
                    array_push($clientAndMessage, [
                        $this->clientHandler->getClientByHash($clientHash),
                        $message->createMessage(
                            "playerLeft",
                            $playerNumber,
                            $state,
                            $winner
                        )
                    ]);
                }
            }
        }

        // notify players of disconnect
        foreach ($clientAndMessage as [$client, $message]) {
            $client->send($message);
        }
    }

    private function validMessage($msg): bool
    {

        return (property_exists($msg, "playerId") &&
            property_exists($msg, "game") &&
            property_exists($msg, "type") &&
            property_exists($msg, "gameId") &&
            property_exists($msg, "position")) &&
            (strlen($msg->playerId) < 37 &&
                $this->gameFactory->isValidGame($msg->game) &&
                key_exists($msg->type, MessageHandler::$messageTypes) &&
                strlen($msg->gameId) < 37 &&
                ((is_null($msg->position) && $msg->type !== "makeMove") ||
                    $this->gameFactory->isValidPositionInGame(
                        $msg->game,
                        $msg->position
                    )));
    }

    public function handleMessage($msg, $client): void
    {

        if (!$this->validMessage($msg)) {
            return;
        }

        $playerId = $msg->playerId;
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
        if (!$this->gameExists($gameId)) {
            return;
        }

        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        $game = $this->games[$gameId];
        if (!$game->isPlayer($playerId)) {
            return;
        }

        $message = new MessageOut($playerId, $gameId);

        $client->send(
            $message->createMessage(
                "inGame",
                $game->getPlayerNumber($playerId),
                $game->getState(),
                $game->getWinner()
            )
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
            $message = new MessageOut($player, $gameId);
            $client->send(
                $message->createMessage(
                    "playerRejoin",
                    $game->getPlayerNumber($playerId),
                    $game->getState(),
                    $game->getWinner()
                )
            );
        }
    }

    private function gameExists($gameId)
    {
        return array_key_exists($gameId, $this->games);
    }

    private function handleMove($gameId, $client1, $position)
    {
        if (!$this->gameExists($gameId)) {
            return;
        }

        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $game = $this->games[$gameId];
        if (!$game->isPlayer($playerId1)) {
            return;
        }

        $playerIds = $game->getPlayers();
        $playerId2 = ($playerId1 === $playerIds[0]) ? $playerIds[1] : $playerIds[0];

        if (!$game->makeMove($playerId1, $position)) {
            return;
        }

        $winner = $game->getWinner();
        $gameOver = $game->gameOver();
        $status = ($gameOver) ?  "gameOver" : "inGame";
        $state = $game->getState();

        $message = new MessageOut($playerId1, $gameId);

        $client1->send($message->createMessage(
            $status,
            $game->getPlayerNumber($playerId1),
            $state,
            $winner
        ));

        if ($this->clientHandler->playerIsConnected($playerId2)) {
            $client2 = $this->clientHandler->getClientByPlayerId($playerId2);
            $message = new MessageOut($playerId2, $gameId);
            $client2->send($message->createMessage(
                $status,
                $game->getPlayerNumber($playerId2),
                $game->getState(),
                $winner
            ));
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
        $client1 = $this->clientHandler->getClientByHash($lobby->shift());
        $client2 = $this->clientHandler->getClientByHash($lobby->shift());
        $this->createGame($gameType, $client1, $client2);
    }

    private function notifyPlayerTheyAreInLobby(ConnectionInterface $client)
    {
        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        $message = new MessageOut($playerId);
        $client->send($message->createMessage("inLobby"));
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

            $message = new MessageOut($playerId1, $game->getId());
            $playerNumber1 = $game->getPlayerNumber($playerId1);
            $playerNumber2 = $game->getPlayerNumber($playerId2);
            $client1->send($message->createMessage("inGame", $playerNumber1));

            $message = new MessageOut($playerId2, $game->getId());
            $client2->send($message->createMessage("inGame", $playerNumber2));
        };
    }
}
