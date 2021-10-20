<?php

use Game\Server\ClientHandler;
use Game\Db\Database;
use Game\GameFactory;
use Game\Server\MessageHandler;
use Game\Session\Session;
use Game\TicTacToe;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;


require __DIR__ . '/vendor/autoload.php';

$develop = true;
$db = new Database($develop);
$db->resetDb();
$gameFactory = new GameFactory();
$gameFactory->addGame(new TicTacToe());
$clientHandler = new ClientHandler($db);

$messageHandler = new MessageHandler($clientHandler, $gameFactory, $db);

$session = Session::establishSession($messageHandler, $develop);

$server = IoServer::factory(
    new HttpServer(
        $session
    ),
    8080
);

$server->run();
