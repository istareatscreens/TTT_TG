<?php

namespace Game;

use Db\Database;
use Db\GameState;
use Db\PlayerState;
use Ratchet\ConnectionInterface;
use Library\Lobby;
use Library\Uuid;

require_once "library/Lobby.php";
require_once "db/GameState.php";

class MessageHandler
{

    private $clientHandler;
    private $lobby;
    private $db;
    private $gameState;

    public function __construct(ClientHandler $clientHandler, Database $db)
    {
        $this->clientHandler = $clientHandler;
        $this->db = $db;
        $this->lobby = new Lobby();
        $this->gameState = new GameState($db);
    }

    public function addClient(ConnectionInterface $client)
    {
        $this->clientHandler->addClient($client);
    }

    public function registerClient(ConnectionInterface $client, string $playerId): bool
    {
        return $this->clientHandler->validateClient($client, $playerId);
    }
    /*
        type: joinLobby | joinGame | makeMove
        gameId: default == -1 | id
        playerId: ""
        quadrant: -1<value<9
    */

    /*
        status: inLobby | inGame | failed
        state: 
        gameId: 
        winner: 0 | 1 | 2
    */

    public function handleMessage($msg, $client): void
    {
        $playerId = $msg->playerId;
        if (!$this->registerClient($client, $playerId)) {
            return;
        }

        switch ($msg->type) {
            case "joinLobby":
                $this->addToLobby($client);
                $this->matchPlayer($client);
                break;
            case "makeMove":
                $this->handleMove($msg, $client);
                break;
            case "joinGame":
                break;
        }
    }

    /*
        status: inLobby | inGame | gameOver
        state: 
        gameId: 
        winner: 0 | 1 | 2
    */
    private function handleMove($msg, $client1)
    {
        $gameId = $msg->gameId;
        if (!array_key_exists($gameId, $this->games)) {
            return;
        }

        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $game = $this->games[$gameId];
        if (!$game->isPlayer($playerId1)) {
            return;
        }

        $playerIds = $game->getPlayers();
        $playerId2 = ($playerId1 === $playerIds[0]) ? $playerIds[1] : $playerIds[0];

        $quadrant = $msg->quadrant;
        if (!$game->makeMove($playerId1, $quadrant)) {
            return;
        }
        $winner = $game->getWinner();
        $gameOver = $game->gameOver();
        $status = ($gameOver) ? "inGame" : "gameOver";

        $message = new MessageOut($playerId1, $gameId);
        $client1->send($message->createMessage($status, $game->getState(), $winner));

        if (!$this->clientHandler->clientIsConnected($playerId2)) {
            $client2 = $this->clientHandler->getClientByPlayerId($playerId2);
            $message = new MessageOut($playerId2, $gameId);
            $client2->send($message->createMessage($status, $game->getState(), $winner));
        }

        if ($gameOver) {
            $this->gameState->deleteGame($gameId);
            unset($this->games[$gameId]);
        }
    }

    private function addToLobby($client): void
    {
        $hash = $this->clientHandler->getClientHash($client);
        $this->lobby->queue($hash);
    }

    private function matchPlayer($client): void
    {
        if (!$this->lobby->isEmpty() && $this->lobby->size() % 2 !== 0) {
            $message = new MessageOut($this->clientHandler->getPlayerIdByClient($client));
            $client->send($message->createMessage("inLobby"));
            return;
        }
        $client1 = $this->clientHandler->getClientByHash($this->lobby->shift());
        $client2 = $this->clientHandler->getClientByHash($this->lobby->shift());
        $this->createGame($client1, $client2);
    }

    private function createGame($client1, $client2): void
    {
        $gameId = Uuid::v4();

        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);

        $this->games[$gameId] = new Game($gameId, $playerId1, $playerId2);
        $this->gameState->createGame($gameId, $playerId1, $playerId2);

        $message = new MessageOut($playerId1, $gameId);
        $client1->send($message->createMessage("inGame"));

        $message = new MessageOut($playerId2, $gameId);
        $client2->send($message->createMessage("inGame"));
    }
}
