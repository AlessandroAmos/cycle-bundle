<?php

declare(strict_types=1);

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('cycle.migration.config', [
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('cycle.migration.config', MigrationConfig::class)
        ->args([
        '%cycle.migration.config%',
    ]);

    $services->alias(MigrationConfig::class, 'cycle.migration.config');

    $services->set('cycle.migration.repository', FileRepository::class)
        ->args([
        service('cycle.migration.config'),
    ]);

    $services->set('cycle.migration.migrator', Migrator::class)
        ->arg('$config', service('cycle.migration.config'))
        ->arg('$dbal', service('cycle.dbal.database_manager'))
        ->arg('$repository', service('cycle.migration.repository'));

    $services->alias(Migrator::class, 'cycle.migration.migrator');

    $services->set('cycle.migration.generate_migrations', GenerateMigrations::class)
        ->arg('$repository', service('cycle.migration.repository'))
        ->arg('$migrationConfig', service('cycle.migration.config'));
};
