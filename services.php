<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Scoutapm\Agent;
use Scoutapm\Config;
use Scoutapm\Config\ConfigKey;
use Scoutapm\ScoutApmAgent;

$services = new ServiceManager();

const ENV_LONG_PROCESS_ITERATION_COUNT = 'LONG_PROCESS_ITERATION_COUNT';
const ENV_WAIT_AFTER_ITERATION_SECS = 'WAIT_AFTER_ITERATION_SECS';
const ENV_SINGLE_ITERATION_SLEEP_SECS = 'SINGLE_ITERATION_SLEEP_SECS';

$numericEnvFactoryFactory = static function (string $envVarName, int $defaultValueIfNegative) {
    return static function () use ($envVarName, $defaultValueIfNegative): int {
        $sleepTime = (int) getenv($envVarName);

        if ($sleepTime <= 0) {
            return $defaultValueIfNegative;
        }

        return $sleepTime;
    };
};

function assertEnvNotEmpty(string $envVarName) {
    $envVarValue = (string) getenv($envVarName);
    if ($envVarValue === '') {
        throw new InvalidArgumentException(sprintf('Environment variable "%s" was required but not set', $envVarName));
    }
}

$services->setFactory(
    ENV_LONG_PROCESS_ITERATION_COUNT,
    $numericEnvFactoryFactory(ENV_LONG_PROCESS_ITERATION_COUNT, 3)
);
$services->setFactory(
    ENV_WAIT_AFTER_ITERATION_SECS,
    $numericEnvFactoryFactory(ENV_WAIT_AFTER_ITERATION_SECS, 60)
);
$services->setFactory(
    ENV_SINGLE_ITERATION_SLEEP_SECS,
    $numericEnvFactoryFactory(ENV_SINGLE_ITERATION_SLEEP_SECS, 3)
);

$services->setFactory(
    LoggerInterface::class,
    static function (): LoggerInterface {
        $logger = new Logger('application');
        $logger->pushHandler(new StreamHandler('php://stderr'));
        return $logger;
    }
);

$services->setFactory(
    ScoutApmAgent::class,
    static function (ContainerInterface $container): ScoutApmAgent {
        assertEnvNotEmpty('SCOUT_KEY');
        assertEnvNotEmpty('SCOUT_NAME');
        assertEnvNotEmpty('SCOUT_MONITOR');
        return Agent::fromConfig(
            Config::fromArray([]), // Relying on environment variables for configuration
            $container->get(LoggerInterface::class),
        );
    }
);

return $services;
