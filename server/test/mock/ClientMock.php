<?php

namespace Test\Mock;

use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

class SessionMock
{
    private string $playerId;

    public function __construct($playerId)
    {
        $this->playerId = $playerId;
    }

    public function get(string $id)
    {
        return $this->playerId;
    }
}

class ClientMock implements ConnectionInterface
{
    public $resourceId;
    public array $messages;
    public $Session;

    public function __construct(string $playerId = "")
    {
        $this->resourceId = Uuid::v4();
        $this->messages = array();
        $this->Session = new SessionMock($playerId);
    }

    public function send($msg)
    {
        array_push($this->messages, $msg);
        return $msg;
    }

    public function getMessage()
    {
        return array_pop($this->messages);
    }

    public function close()
    {
        return NULL;
    }
}
