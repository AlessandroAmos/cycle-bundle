<?php

declare(strict_types=1);

use Alms\Bundle\CycleBundle\Database\DatabaseConfigFactory;
use Cycle\Database\Config\ConnectionConfig;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\DriverConfig;
use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();


    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('cycle.dbal.config_factory', DatabaseConfigFactory::class);

    $services->set('cycle.dbal.connection_config', ConnectionConfig::class)
        ->factory([
            service('cycle.dbal.config_factory'),
            'createConnectionConfig',
        ])
        ->abstract(true);

    $services->set('cycle.dbal.driver_config', DriverConfig::class)
        ->factory([
            service('cycle.dbal.config_factory'),
            'createDriverConfig',
        ])
        ->abstract(true);

    $services->set('cycle.dbal.database_config', DatabaseConfig::class)
        ->factory([
            service('cycle.dbal.config_factory'),
            'createDatabaseConfig',
        ])
        ->arg('$connections', null);

    $services->alias(DatabaseConfig::class, 'cycle.dbal.database_config');

    $services->set('cycle.dbal.database_manager', DatabaseManager::class)
        ->arg('$config', service('cycle.dbal.database_config'));

    $services->alias(DatabaseManager::class, 'cycle.dbal.database_manager')->public();
    $services->alias(DatabaseProviderInterface::class, 'cycle.dbal.database_manager')->public();

    $services->set('cycle.dbal.database', Database::class)
        ->factory([
            service('cycle.dbal.database_manager'),
            'database',
        ]);

    $services->alias(DatabaseInterface::class, 'cycle.dbal.database')->public();
};