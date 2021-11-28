--TEST--
swoole_client_sync: long connection[6]
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php declare(strict_types = 1);
require __DIR__ . '/../include/bootstrap.php';

$pm = new ProcessManager;

$pm->parentFunc = function () use ($pm) {

    $client1 = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP | SWOOLE_SYNC);
    $r = @$client1->connect(TCP_SERVER_HOST, $pm->getFreePort(), 0.5);
    Assert::true($r);
    $client1->send("hello");
    echo $client1->recv();
    $client1->close();

    usleep(10000);

    $client2 = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP | SWOOLE_SYNC);
    /**
     * recreate socket
     */
    $r = $client2->connect(TCP_SERVER_HOST, $pm->getFreePort(), 0.5);
    Assert::true($r);
    $client2->send("hello");
    echo $client2->recv();
    $client2->close();

    $pm->kill();
};

$pm->childFunc = function () use ($pm) {
    $server = new swoole_server('127.0.0.1', $pm->getFreePort(), SWOOLE_BASE);
    $server->set(['worker_num' => 1, 'log_file' => '/dev/null']);
    $server->on('workerStart', function () use ($pm) {
        $pm->wakeup();
    });
    $server->on('receive', function (swoole_server $serv, $fd, $tid, $data) {
        $serv->send($fd, "Swoole $data\n");
        usleep(5000);
        $serv->close($fd);
    });
    $server->start();
};

$pm->childFirst();
$pm->run();

?>
--EXPECT--
Swoole hello
Swoole hello
