<?php


namespace Test;

use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Library\Uuid;
use Game\Server\ClientHandler;
use Game\TicTacToe;
use Test\Mock\ClientMock;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class ClientHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database();
        $this->db->resetDb();
        $this->clientHandler = new ClientHandler($this->db);
        $this->playerState = new PlayerState($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->resetDb();
    }

    /** @test */
    public function validateClient_no_hash()
    {
        // No hash
        $client = new ClientMock();
        $client->resourceId = "";
        $result = $this->clientHandler->validateClient($client, "");
        $this->assertFalse($result);
    }

    /** @test */
    public function validateClient_no_playerId()
    {
        //register client (no playerId)
        $client1 = new ClientMock();
        $this->clientHandler->addClient($client1);
        $result = $this->clientHandler->validateClient($client1, "");
        $this->assertTrue($result);

        //valid player correct hash
        $client1 = new ClientMock();
        $this->clientHandler->addClient($client1);
        $playerId1 = "ad27d471-9448-46c8-8142-eadbac1f6706";
        $this->playerState->savePlayer($playerId1, $client1->resourceId);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);

        //valid hash changed playerId
        $playerId2 = "22349f33-7af2-4aa8-9f66-804a7630b3ea";
        $result = $this->clientHandler->validateClient($client1, $playerId2);
        $this->assertFalse($result);

        //different hash valid playerId
        $client1 = new ClientMock();
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);

        //invalid hash invalid playerId (proper uuid)
        $client1 = new ClientMock();
        $result = $this->clientHandler->validateClient($client1, $playerId2);
        $this->assertFalse($result);
    }

    /** @test */
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
    public function test_removeClient_and_isConnected()
    {
        //valid player correct hash
        $client1 = new ClientMock();
        $this->clientHandler->addClient($client1);
        $playerId1 = "4df0cbda-df38-4ebd-9ed4-f1e9043ad699";
        $this->playerState->savePlayer($playerId1, $client1->resourceId);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);
        $this->assertTrue($this->clientHandler->playerIsConnected($playerId1));

        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertEquals($result->client_hash, $client1->resourceId);

        $this->clientHandler->removeClient($client1->resourceId);
        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertEquals($result->client_hash, "0");
        $this->assertFalse($this->clientHandler->playerIsConnected($playerId1));
    }
}
