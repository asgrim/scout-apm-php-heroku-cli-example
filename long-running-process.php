<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Scoutapm\ScoutApmAgent;

require_once __DIR__ . '/vendor/autoload.php';

/** @var ContainerInterface $services */
$services = require __DIR__ . '/services.php';

$scout = $services->get(ScoutApmAgent::class);
$logger = $services->get(LoggerInterface::class);

$scout->connect();

$iterationCount = $services->get(ENV_LONG_PROCESS_ITERATION_COUNT);
$sleepSecs = $services->get(ENV_SINGLE_ITERATION_SLEEP_SECS);
$waitSecs = $services->get(ENV_WAIT_AFTER_ITERATION_SECS);

for ($i = 0; $i < $iterationCount; $i++) {
    $name = 'HerokuLongRunningProcessTransaction-' . date('Y-m-d-H-i-s');
    $logger->debug('STARTING: ' . $name);
    $scout->backgroundTransaction(
        $name,
        static function () use ($sleepSecs) {
            sleep($sleepSecs);
        }
    );

    $scout->send();

    $logger->debug('FINISHED: ' . $name);
    sleep($waitSecs);
}
