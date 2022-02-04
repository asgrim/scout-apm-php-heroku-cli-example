<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Scoutapm\ScoutApmAgent;

require_once __DIR__ . '/vendor/autoload.php';

/** @var ContainerInterface $services */
$services = require __DIR__ . '/services.php';
$logger = $services->get(LoggerInterface::class);

$scout = $services->get(ScoutApmAgent::class);

$scout->connect();

$name = 'HerokuSingleTransaction-' . date('Y-m-d-H-i-s');
$logger->debug('STARTING: ' . $name);
$scout->backgroundTransaction(
    $name,
    static function () use ($services) {
        sleep($services->get(ENV_SINGLE_ITERATION_SLEEP_SECS));
    }
);

$scout->send();

$logger->debug('FINISHED: ' . $name);
