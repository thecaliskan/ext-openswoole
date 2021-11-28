--TEST--
swoole_server/task: task in manager
--SKIPIF--
<?php require __DIR__ . '/../../include/skipif.inc'; ?>
--FILE--
<?php declare(strict_types = 1);
require __DIR__ . '/../../include/bootstrap.php';

use Swoole\Server;
$pm = new SwooleTest\ProcessManager;
$pm->setWaitTimeout(60);
$pm->parentFunc = function ($pid) use ($pm) {
    echo "SUCCESS\n";
    $pm->kill();
};

$pm->childFunc = function () use ($pm)
{
    ini_set('swoole.display_errors', 'Off');
    $serv = new Server('127.0.0.1', $pm->getFreePort());
    $serv->set(array(
        "worker_num" => 1,
        'task_worker_num' => 2,
        'log_file' => '/dev/null',
    ));
    $serv->on("managerStart", function (Server $serv)  use ($pm)
    {
        $serv->task(['type' => 'array', 'value' => 'manager']);
    });
    $serv->on('receive', function (Server $serv, $fd, $rid, $data)
    {

    });

    $serv->on('task', function (Server $serv, $task_id, $worker_id, $data) use($pm)
    {
        Assert::false($serv->finish("OK"));
        $pm->wakeup();
    });

    $serv->on('finish', function (Server $serv, $fd, $rid, $data)
    {

    });
    $serv->start();
};

$pm->childFirst();
$pm->run();
?>
--EXPECT--
SUCCESS
