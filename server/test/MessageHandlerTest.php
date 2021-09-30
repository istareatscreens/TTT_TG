<?php


namespace Test;

use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Db\GameState;
use Game\Library\Uuid;
use Game\Server\ClientHandler;
use Game\Server\MessageOut;
use Game\Test\Mock\ClientMock;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class MessageHandler extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database();
        $this->db->resetDb();

        $this->playerState = new PlayerState($this->db);
        $this->gameState = new GameState($this->db);
        $this->clientHandler = new ClientHandler($this->db);
        $this->messageHandler = new MessageHandler($this->clientHandler, $this->db);

        $this->client1 = new ClientMock();
        $this->playerId1 = "ad27d471-9448-46c8-8142-eadbac1f6706";
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
        string $status,
        string $gameId,
        int $state,
        string $winner
    ) {
        return [
            "status" => $status,
            "gameId" => $gameId,
            "state" => $state,
            "winner" => $winner
        ];
    }

    /** @test */
    public function messageHandler_join_lobby()
    {
        $this->msg = new MessageOut($this->playerId1);

        //$this->messageHandler($this->cleint1,);
    }

    public function test_getters()
    {
        //valid player correct hash
        $client1 = new ClientMock();
        $this->clientHandler->addClient($client1);
        $playerId1 = "4df0cbda-df38-4ebd-9ed4-f1e9043ad699";
        $this->playerState->savePlayer($playerId1, $client1->resourceId);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);

        $result = $this->clientHandler->getClientByPlayerId($playerId1);
        $this->assertEquals($result, $client1);
        $result = $this->clientHandler->getClientByHash($client1->resourceId);
        $this->assertEquals($result, $client1);
        $result = $this->clientHandler->getPlayerIdByClient($client1);
        $this->assertEquals($result, $playerId1);
        $result = $this->clientHandler->getClientHash($client1);
        $this->assertEquals($result, $client1->resourceId);
    }

    /** @test */
    public function test_removeClient()
    {
        //valid player correct hash
        $client1 = new ClientMock();
        $this->clientHandler->addClient($client1);
        $playerId1 = "4df0cbda-df38-4ebd-9ed4-f1e9043ad699";
        $this->playerState->savePlayer($playerId1, $client1->resourceId);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);

        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertEquals($result->client_hash, $client1->resourceId);

        $this->clientHandler->removeClient($client1->resourceId);
        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertEquals($result->client_hash, "0");
    }
}
