<?php
/* source: http://socketo.me/docs/hello-world */

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Game\SocketServer;
use Game\MessageHandler;
use Db\Database;
use Game\ClientHandler;

require __DIR__ . '/vendor/autoload.php';
require_once "SocketServer.php";
require_once "db/Database.php";
require_once "ClientHandler.php";
require_once "MessageHandler.php";

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
