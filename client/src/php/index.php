<?php
ob_start();

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Client\Library\Uuid;

require __DIR__ . '/vendor/autoload.php';
require_once "Uuid.php";

$memcache = new Memcached;
if (!count($memcache->getServerList())) {
    $memcache->addServer('memcached', 11211);
}

$storage = new NativeSessionStorage(
    array(),
    new MemcachedSessionHandler($memcache)
);

$session = new Session($storage);
$session->start();
$uuid = Uuid::v4();
$session->set('id', $uuid);
ob_end_flush();

require_once "home.php";
