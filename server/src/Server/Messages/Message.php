<?php

namespace Game\Server\Messages;

abstract class Message
{
    protected array $message;

    public function __construct()
    {
        $this->message = [];
    }

    protected function getMessageArray(): array
    {
        return $this->message;
    }

    public function addTo(Message $message): Message
    {
        $this->message = array_merge($message->getMessageArray(), $this->message);
        return $this;
    }

    public function getMessage(): string
    {
        return json_encode($this->message);
    }
}
