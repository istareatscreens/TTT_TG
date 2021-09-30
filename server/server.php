<?php
/* source: http://socketo.me/docs/hello-world */

use Game\Server\ClientHandler;
use Game\Db\Database;
use Game\GameFactory;
use Game\Server\MessageHandler;
use Game\Server\SocketServer;
use Game\TicTacToe;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

$db = new Database();
$gameFactory = new GameFactory();
$gameFactory->addGame(new TicTacToe());
$clientHandler = new ClientHandler($db);

$messageHandler = new MessageHandler($clientHandler, $gameFactory, $db);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SocketServer($messageHandler)
        )
    ),
    8080
);

$server->run();
