<?php

declare(strict_types=1);

use Alms\Bundle\CycleBundle\Command\Database\ListCommand;
use Alms\Bundle\CycleBundle\Command\Database\TableCommand;
use Alms\Bundle\CycleBundle\Command\Migration\InitCommand;
use Alms\Bundle\CycleBundle\Command\Migration\MigrateCommand;
use Alms\Bundle\CycleBundle\Command\Migration\ReplayCommand;
use Alms\Bundle\CycleBundle\Command\Migration\RollbackCommand;
use Alms\Bundle\CycleBundle\Command\Migration\StatusCommand;
use Alms\Bundle\CycleBundle\Command\Schema\RenderCommand;
use Alms\Bundle\CycleBundle\Command\Schema\SyncCommand;
use Alms\Bundle\CycleBundle\Command\Schema\UpdateCommand;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('cycle.core.inflector_factory.en', InflectorFactory::class)
        ->factory([
            \Doctrine\Inflector\InflectorFactory::class,
            'create',
        ]);

    $services->set('cycle.core.inflector', Inflector::class)
        ->factory([
            service('cycle.core.inflector_factory.en'),
            'build',
        ]);

    $services->set('cycle.command.dbal.list', ListCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.dbal.table', TableCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.migration.init', InitCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.migration.migrate', MigrateCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.migration.replay', ReplayCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.migration.rollback', RollbackCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.migration.status', StatusCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.schema.migrate', \Alms\Bundle\CycleBundle\Command\Schema\MigrateCommand::class)
        ->arg('$registryFactory', service('cycle.schema.registry_factory'))
        ->arg('$migrations', service('cycle.migration.generate_migrations'))
        ->tag('console.command');

    $services->set('cycle.command.schema.render', RenderCommand::class)
        ->tag('console.command');

    $services->set('cycle.command.schema.sync', SyncCommand::class)
        ->arg('$registryFactory', service('cycle.schema.registry_factory'))
        ->arg('$syncTables', service('cycle.schema.generator.sync_tables'))
        ->tag('console.command');

    $services->set('cycle.command.schema.update', UpdateCommand::class)
        ->tag('console.command');
};
