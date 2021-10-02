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

    /*
        type: joinLobby | joinGame | makeMove
        gameId: default == -1 | id
        playerId: ""
        quadrant: -1<value<9
    */

    /*
        status: inLobby | inGame | failed
        state: 
        gameId: 
        winner: 0 | 1 | 2
    */

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        //template code
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n",
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
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
