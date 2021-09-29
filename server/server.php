<?php
/* source: http://socketo.me/docs/hello-world */

use Game\Server\ClientHandler;
use Game\Db\Database;
use Game\Server\MessageHandler;
use Game\Server\SocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';
//require_once realpath("vender/autoload.php");

$db = new Database();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SocketServer(new MessageHandler(new ClientHandler($db), $db))
        )
    ),
    8080
);

$server->run();
