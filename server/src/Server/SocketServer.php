<?php

namespace Game\Server;

use GameFactory;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketServer implements MessageComponentInterface
{
    protected $messageHandler;

    public function __construct(MessageHandler $messageHandler)
    {
        $this->messageHandler = $messageHandler;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->messageHandler->addClient($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        //template code
        echo sprintf(
            'Recieved from %d message: "%s"' .
                "\n",
            $from->resourceId,
            $msg,
        );
        $msg = json_decode($msg);
        $this->messageHandler->handleMessage($msg, $from);
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->messageHandler->disconnectClient($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $this->messageHandler->disconnectClient($conn);
        $conn->close();
    }
}
