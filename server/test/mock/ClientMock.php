<?php

namespace Test\Mock;

use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

class ClientMock implements ConnectionInterface
{
    public $resourceId;
    public array $messages;

    public function __construct()
    {
        $this->resourceId = Uuid::v4();
        $this->messages = array();
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
