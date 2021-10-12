<?php

namespace Game\Session;

use Game\Server\MessageHandler;
use Game\Server\SocketServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;

class Session
{

    public static function establishSession(MessageHandler $messageHandler, bool $develop): SessionProvider | WsServer
    {
        $wsServer = Session::getSocketServer($messageHandler, $develop);

        if ($develop) {
            return $wsServer;
        }

        $memcache = new \Memcached;
        $memcache->addServer('memcached', 11211);

        return new SessionProvider(
            $wsServer,
            new Handler\MemcachedSessionHandler($memcache)
        );
    }

    private static function getSocketServer(MessageHandler $messageHandler, bool $develop): WsServer
    {
        return new WsServer(
            new SocketServer($messageHandler, $develop)
        );
    }
}
