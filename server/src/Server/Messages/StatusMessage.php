<?php

namespace Game\Server\Messages;

class StatusMessage extends Message
{
    public function __construct($status)
    {
        $this->message["status"] = $status;
    }
}
