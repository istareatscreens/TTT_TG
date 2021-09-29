<?php

namespace Game\Test;

use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

class ClientMock implements ConnectionInterface
{
    public $resourceId;

    public function __construct()
    {
        $this->resourceId = Uuid::v4();
    }

    public function send($data)
    {
        return $data;
    }

    public function close()
    {
        return NULL;
    }
}
