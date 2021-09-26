<?php
/* source: http://socketo.me/docs/hello-world */
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use GameClient\SocketClient;

require __DIR__.'/vendor/autoload.php';
require_once "SocketClient.php";

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new SocketClient()
            )
        ),
        8080
    );

    $server->run();