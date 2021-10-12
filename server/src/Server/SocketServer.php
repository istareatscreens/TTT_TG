<?php

namespace Game\Server;

use Exception;
use Game\Library\Uuid;
use Psr\Http\Message\RequestInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketServer implements MessageComponentInterface
{
    protected MessageHandler $messageHandler;
    private bool $develop;

    public function __construct(MessageHandler $messageHandler, bool $develop)
    {
        $this->messageHandler = $messageHandler;
        $this->develop = $develop;
    }

    private function getSessionId(ConnectionInterface $conn): string
    {
        try {
            return $this->develop ?  Uuid::v4() : $conn->Session->get('id');
        } catch (Exception $e) {
            return "";
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        if ($this->develop) {
            echo "\nNew connection! ({$conn->resourceId})\n";
            echo "\nSESSION ID: " . $conn->Session->get('id');
        }

        $playerId = $this->getSessionId($conn);
        if (!$this->messageHandler->addClient($conn, $playerId)) {
            $conn->close();
        }
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
        $playerId = $this->getSessionId($from);

        $this->messageHandler->handleMessage($from, $msg, $playerId);
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
