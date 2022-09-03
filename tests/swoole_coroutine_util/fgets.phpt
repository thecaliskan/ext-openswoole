--TEST--
swoole_coroutine_util: fgets
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php declare(strict_types = 1);
require __DIR__ . '/../include/bootstrap.php';

Co::set(['hook_flags' => 0]);

co::run(function () {
    $file = __DIR__ . '/../../examples/server.php';

    $coroutine = '';
    $fp = fopen($file, "r");
    while (!feof($fp)) {
        $coroutine .= co::fgets($fp);
    }

    $standard = '';
    $fp = fopen($file, "r");
    while (!feof($fp)) {
        $standard .= fgets($fp);
    }

    Swoole\Runtime::enableCoroutine();
    $runtime = '';
    $fp = fopen($file, "r");
    while (!feof($fp)) {
        $runtime .= fgets($fp);
    }

    Assert::same($standard, $coroutine);
    Assert::same($standard, $runtime);

    echo "DONE\n";
});
?>
--EXPECT--
DONE
