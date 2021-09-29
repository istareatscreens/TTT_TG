<?php

namespace Game\Test;

use Game\Db\Database;
use Game\Server\ClientHandler;
use PHPUnit\Framework\TestCase;

class ClientHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database();
        $this->messageHandler = new ClientHandler($this->db);
    }
    /** @test */
    public function add_client_no_hash()
    {
        $client = new ClientMock();
        $client->resourceId = "";
        $result = $this->messageHandler->validateClient($client, "");
        $this->assertFalse($result);
    }
}
