<?php

use Game\Server\ClientHandler;
use Game\Db\Database;
use Game\Game\GameFactory;
use Game\Server\MessageHandler;
use Game\Session\Session;
use Game\Game\TicTacToe;
use Game\Game\QTicTacToe\QTicTacToe;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;


require __DIR__ . '/vendor/autoload.php';

$develop = false;
$db = new Database($develop);
$db->resetDb();
$gameFactory = new GameFactory();
$gameFactory->addGame(new TicTacToe());
$gameFactory->addGame(new QTicTacToe());
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
