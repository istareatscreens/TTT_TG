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
    private Database $db;
    private GameState $gameState;
    private GameFactory $gameFactory;

    public function __construct(ClientHandler $clientHandler, GameFactory $gameFactory, Database $db)
    {
        $this->clientHandler = $clientHandler;
        $this->db = $db;
        $this->lobbies = array();
        $this->gameState = new GameState($db);
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
        $gameType = $msg->game;
        $type = $msg->type;
        if (!$this->registerClient($client, $playerId)) {
            return;
        }

        echo $msg->type;
        switch ($type) {
            case "joinLobby":
                echo "here";
                $this->addToLobby($gameType, $client);
                $this->matchPlayer($gameType, $client);
                break;
            case "makeMove":
                $this->handleMove($msg, $client);
                break;
            case "joinGame":
                break;
        }
    }

    private function affixNamespaceToGameType($gameType)
    {
        return "Game\\" . $gameType;
    }

    private function removeNamespaceFromGameType($gameType)
    {
        return substr($gameType, strlen("Game\\"));
    }

    /*
        status: inLobby | inGame | gameOver
        state: 
        mark:
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

        $message = new MessageOut($playerId1, $gameId, $game->getPlayerNumber($playerId1));
        $client1->send($message->createMessage($status, $game->getState(), $winner));

        if (!$this->clientHandler->playerIsConnected($playerId2)) {
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
            $this->notifyPlayerTheyAreInLobby($gameType, $client);
            return;
        }
        $client1 = $this->clientHandler->getClientByHash($lobby->shift());
        $client2 = $this->clientHandler->getClientByHash($lobby->shift());
        $this->createGame($gameType, $client1, $client2);
    }

    private function notifyPlayerTheyAreInLobby(string $gameType, ConnectionInterface $client)
    {
        $playerId = $this->clientHandler->getPlayerIdByClient($client);
        $message = new MessageOut($gameType, $playerId);
        $client->send($message->createMessage("inLobby"));
    }

    private function createGame($gameType, $client1, $client2): void
    {
        $gameId = Uuid::v4();
        $gameClass = $this->affixNamespaceToGameType($gameType);

        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);

        if ($game = $this->gameFactory->createGame($gameClass, $gameId, $playerId1, $playerId2)) {

            $this->games[$gameId] = $game;
            $this->gameState->createGame($gameId, $playerId1, $playerId2);

            $message = new MessageOut($gameType, $playerId1, $gameId);
            $client1->send($message->createMessage("inGame"));

            $message = new MessageOut($gameType, $playerId2, $gameId);
            $client2->send($message->createMessage("inGame"));
        };
    }
}
