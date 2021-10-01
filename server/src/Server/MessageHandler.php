<?php

namespace Game\Server;

use Game\Db\Database;
use Game\Db\GameState;
use Game\GameFactory;
use Game\TicTacToe;
use Game\Library\Lobby;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

class MessageHandler
{

    private ClientHandler $clientHandler;
    private array $games;
    private array $lobbies;
    private GameState $gameState;
    private GameFactory $gameFactory;

    public function __construct(ClientHandler $clientHandler, GameFactory $gameFactory, Database $db)
    {
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

    public function registerClient(ConnectionInterface $client, string $playerId): bool
    {
        return $this->clientHandler->validateClient($client, $playerId);
    }

    public function handleMessage($msg, $client): void
    {
        $playerId = $msg->playerId;
        $gameType = $msg->game;
        $type = $msg->type;
        $gameId = $msg->gameId;
        $position = $msg->position;

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
                $this->joinGame($gameId, $client);
                return;
        }
    }

    private function affixNamespaceToGameType($gameType)
    {
        return "Game\\" . $gameType;
    }

    private function joinGame($gameId, $client)
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

        $client1->send($message->createMessage($status, $game->getPlayerNumber($playerId1), $state, $winner));

        if ($this->clientHandler->playerIsConnected($playerId2)) {
            $client2 = $this->clientHandler->getClientByPlayerId($playerId2);
            $message = new MessageOut($playerId2, $gameId);
            $client2->send($message->createMessage($status, $game->getPlayerNumber($playerId2), $game->getState(), $winner));
        }

        if ($gameOver) {
            $this->gameState->deleteGame($gameId);
            unset($this->games[$gameId]);
        }
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

    private function matchPlayer(string $gameType, ConnectionInterface $client): void
    {

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

        $game = $this->gameFactory->createGame($gameType, $gameId, $playerId1, $playerId2);
        if ($game && $this->gameState->createGame($game->getId(), $playerId1, $playerId2)) {
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
