<?php


namespace Test;

use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Server\ClientHandler;
use Test\Mock\ClientMock;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class ClientHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database(true);
        $this->db->resetDb();
        $this->clientHandler = new ClientHandler($this->db);
        $this->playerState = new PlayerState($this->db);

        $this->mockSocketServer = $this->createMock(SocketServer::class);
        $this->mockSocketServer->method("onClose")->willReturn(0);
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
        $result = $this->clientHandler->addClient($client1, "", $this->mockSocketServer);
        $this->assertFalse($result);
        $result = $this->clientHandler->validateClient($client1, "");
        $this->assertFalse($result);

        //valid player correct hash
        $client1 = new ClientMock();
        $playerId1 = "ad27d471-9448-46c8-8142-eadbac1f6706";
        $result = $this->clientHandler->addClient($client1, $playerId1, $this->mockSocketServer);
        $this->assertTrue($result);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);

        //valid hash changed playerId
        $playerId2 = "22349f33-7af2-4aa8-9f66-804a7630b3ea";
        $result = $this->clientHandler->validateClient($client1, $playerId2);
        $this->assertFalse($result);

        //different hash valid playerId
        $client1 = new ClientMock();
        $result = $this->clientHandler->addClient($client1, $playerId1, $this->mockSocketServer);
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
        $playerId1 = "652f4035-d953-4b1c-b78d-338a2fff79cd";
        $result = $this->clientHandler->addClient($client1, $playerId1, $this->mockScoketServer);
        $this->assertTrue($result);
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
        $playerId1 = "27e1eb63-a16c-4d6f-8ac3-9bfba20abfe6";
        $this->clientHandler->addClient($client1, $playerId1, $this->mockSocketServer);
        $result = $this->clientHandler->validateClient($client1, $playerId1);
        $this->assertTrue($result);
        $this->assertTrue($this->clientHandler->playerIsConnected($playerId1));

        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertEquals($result->client_hash, $client1->resourceId);

        $this->clientHandler->removeClient($client1);
        $result = $this->playerState->getPlayerDataFromToken($playerId1);
        $this->assertNull($result->client_hash);
        $this->assertFalse($this->clientHandler->playerIsConnected($playerId1));
    }
}
