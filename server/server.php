<?php
/* source: http://socketo.me/docs/hello-world */
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use GameClient\Client;

require __DIR__.'/vendor/autoload.php';
require_once "Client.php";

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Client()
            )
        ),
        8080
    );

    $server->run();