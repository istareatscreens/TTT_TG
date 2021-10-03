<?php


namespace Test;

use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Db\GameState;
use Game\GameFactory;
use Game\Server\ClientHandler;
use Game\Server\MessageOut;
use Test\Mock\ClientMock;
use Game\TicTacToe;
use Game\Server\MessageHandler;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once "Mock/ClientMock.php";

class MessageHandlerTest extends TestCase
{

    private function removeNamespaceFromType($game)
    {
        return (new ReflectionClass($game))->getShortName();
    }

    protected function setUp(): void
    {
        $this->db = new Database();
        $this->db->resetDb();
        $this->gameState = new GameState($this->db);

        $this->playerState = new PlayerState($this->db);
        $this->gameState = new GameState($this->db);
        $this->clientHandler = new ClientHandler($this->db);
        $this->gameFactory = new GameFactory();

        $this->mockGame = $this->createMock(TicTacToe::class);
        $this->gameName = $this->removeNamespaceFromType($this->mockGame);
        $this->state = 55;
        $this->mockGame->method("createGame")->willReturn($this->mockGame);
        $this->mockGame->method("validPosition")->willReturn(true);
        $this->mockGame->method("getPlayerNumber")->willReturn(1);
        $this->mockGame->method("getState")->willReturn($this->state);
        $this->mockGame->method("getWinner")->willReturn(0);
        $this->mockGame->method("gameOver")->willReturnOnConsecutiveCalls(
            false,
            true
        );
        $this->mockGame->method("makeMove")->willReturn(true);
        $this->mockGame->method("isPlayer")->willReturn(true);

        $this->gameId = "c2fe7ead-af71-44ff-84c3-5ebd17afda85";
        $this->mockGame->method("getId")->willReturn($this->gameId);
        $this->gameFactory->addGame($this->mockGame);

        $this->messageHandler = new MessageHandler($this->clientHandler, $this->gameFactory, $this->db);
        $this->client1 = new ClientMock();
        $this->playerId1 = "3885efd7-719e-4055-aff7-e341bc83629a";
        $this->playerState->savePlayer($this->playerId1, $this->client1->resourceId);
        $this->client2 = new ClientMock();
        $this->playerId2 = "22349f33-7af2-4aa8-9f66-804a7630b3ea";
        $this->playerState->savePlayer($this->playerId2, $this->client2->resourceId);
        $this->client3 = new ClientMock();
        $this->playerId3 = "4df0cbda-df38-4ebd-9ed4-f1e9043ad699";
        $this->playerState->savePlayer($this->playerId3, $this->client3->resourceId);
    }


    protected function tearDown(): void
    {
        $this->db->resetDb();
    }

    private function makeMessageIn(
        string $type,
        string $gameType,
        string |int $gameId = -1,
        string |int $playerId = -1,
        string |int $position = NULL
    ) {
        return (object)[
            "type" => $type,
            "game" => $gameType,
            "gameId" => $gameId,
            "playerId" => $playerId,
            "position" => $position
        ];
    }

    /** @test */
    public function join_lobby_start_game_rejoin_game_make_move_end_game()
    {
        //join loby
        $client1 = new ClientMock();
        $this->messageHandler->addClient($client1);
        $msgIn = $this->makeMessageIn("joinLobby", $this->gameName);
        $this->messageHandler->handleMessage($msgIn, $client1);
        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $msg1 = new MessageOut($playerId1);
        $msgOut1 = $msg1->createMessage("inLobby");
        $this->assertEquals($msgOut1, $client1->getMessage());

        //start game
        $client2 = new ClientMock();
        $this->messageHandler->addClient($client2);
        $msgIn = $this->makeMessageIn("joinLobby", $this->gameName);
        $this->messageHandler->handleMessage($msgIn, $client2);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);
        $msg2 = new MessageOut($playerId2, $this->gameId);
        $msg1 = new MessageOut($playerId1, $this->gameId);
        $msgOut2 = $msg2->createMessage("inGame", 1);
        $this->assertEquals($msgOut2, $client2->getMessage());
        $msgOut1 = $msg1->createMessage("inGame", 1);
        $this->assertEquals($msgOut1, $client1->getMessage());

        //make move
        $this->mockGame->method("getPlayers")->willReturn([$playerId1, $playerId2]);
        $msgIn = $this->makeMessageIn("makeMove", $this->gameName, $this->gameId, $playerId1, 2);
        $this->messageHandler->handleMessage($msgIn, $client1);
        $msgOut1 = $msg1->createMessage("inGame", 1, 55);
        $msgOut2 = $msg2->createMessage("inGame", 1, 55);
        $this->assertEquals($msgOut1, $client1->getMessage());
        $this->assertEquals($msgOut2, $client2->getMessage());

        //join game
        $client2 = new ClientMock();
        $msgIn = $this->makeMessageIn("joinGame", $this->gameName, $this->gameId, $playerId2, 2);
        $this->messageHandler->handleMessage($msgIn, $client2);
        $msgOut2 = $msg2->createMessage("inGame", 1, 55);
        $this->assertEquals($msgOut2, $client2->getMessage());
        $msgOut1 = $msg1->createMessage("playerRejoin", 1, 55);
        $this->assertEquals($msgOut1, $client1->getMessage());

        //leave game and change client
        $this->messageHandler->disconnectClient($client2);
        $msgOut1 = $msg1->createMessage("playerLeft", 1, 55);
        $this->assertEquals($msgOut1, $client1->getMessage());
        $client2 = new ClientMock();
        $msgIn = $this->makeMessageIn("joinGame", $this->gameName, $this->gameId, $playerId2);
        $this->messageHandler->handleMessage($msgIn, $client2);
        $msgOut2 = $msg2->createMessage("inGame", 1, 55);
        $this->assertEquals($msgOut2, $client2->getMessage());


        //game over
        $msgIn = $this->makeMessageIn("makeMove", $this->gameName, $this->gameId, $playerId2, 0);
        $this->mockGame->method("gameOver")->willReturn(true);
        $this->messageHandler->handleMessage($msgIn, $client2);
        $msgOut2 = $msg2->createMessage("gameOver", 1, 55);
        $msgOut1 = $msg1->createMessage("gameOver", 1, 55);
        $this->assertEquals($msgOut1, $client1->getMessage());
        $this->assertEquals($msgOut2, $client2->getMessage());
    }

    public function start_game_both_players_leave()
    {

        //join loby
        $client1 = new ClientMock();
        $this->messageHandler->addClient($client1);
        $msgIn = $this->makeMessageIn("joinLobby", $this->gameName);
        $this->messageHandler->handleMessage($msgIn, $client1);
        $playerId1 = $this->clientHandler->getPlayerIdByClient($client1);
        $msg1 = new MessageOut($playerId1);
        $msgOut1 = $msg1->createMessage("inLobby");
        $this->assertEquals($msgOut1, $client1->getMessage());

        // start game
        $client2 = new ClientMock();
        $this->messageHandler->addClient($client2);
        $msgIn = $this->makeMessageIn("joinLobby", $this->gameName);
        $this->messageHandler->handleMessage($msgIn, $client2);
        $playerId2 = $this->clientHandler->getPlayerIdByClient($client2);
        $msg2 = new MessageOut($playerId2, $this->gameId);
        $msg1 = new MessageOut($playerId1, $this->gameId);
        $msgOut2 = $msg2->createMessage("inGame", 1);
        $this->assertEquals($msgOut2, $client2->getMessage());
        $msgOut1 = $msg1->createMessage("inGame", 1);
        $this->assertEquals($msgOut1, $client1->getMessage());

        // check if game is deleted since all users left
        $this->messageHandler->disconnectClient($client1);
        $msgOut2 = $msg2->createMessage("playerLeft", 1, 55);
        $this->assertEquals($msgOut2, $client1->getMessage());
        $this->messageHandler->disconnectClient($client2);
        $result = $this->gameState->getGame($this->gameId);
        $this->assertTrue(count($result) === 0);

        // should not allow move on game since it was deleted
        $msgIn = $this->makeMessageIn("makeMove", $this->gameName, $this->gameId, $playerId1);
        $this->messageHandler->handleMessage($client1, $msgOut1);
        $this->assertNull($client1->getMessage());
    }
}
