<?php

use Game\Server\ClientHandler;
use Game\Db\Database;
use Game\GameFactory;
use Game\Server\MessageHandler;
use Game\Server\SocketServer;
use Game\TicTacToe;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;

require __DIR__ . '/vendor/autoload.php';

$db = new Database();
$db->resetDb();
$gameFactory = new GameFactory();
$gameFactory->addGame(new TicTacToe());
$clientHandler = new ClientHandler($db);

$messageHandler = new MessageHandler($clientHandler, $gameFactory, $db);

$memcache = new Memcached;
$memcache->addServer('memcached', 11211);

$session = new SessionProvider(
    new WsServer(
        new SocketServer($messageHandler)
    ),
    new Handler\MemcachedSessionHandler($memcache)
);


$server = IoServer::factory(
    new HttpServer(
        $session
    ),
    8080
);

$server->run();
//echo "server listening on 8080\n";
