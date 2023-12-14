<?php

declare(strict_types=1);

use Alms\Bundle\CycleBundle\Schema\Generator\ApplyRelationDefaults;
use Alms\Bundle\CycleBundle\Schema\GeneratorCollection;
use Alms\Bundle\CycleBundle\Schema\Loader\AnnotationLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\ClosureLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\ContainerLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\DirectoryLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\GlobFileLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\Psr4DirectoryLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\SchemaLoader;
use Alms\Bundle\CycleBundle\Schema\Loader\XmlFileLoader;
use Alms\Bundle\CycleBundle\Schema\Registry\RegistryFactory;
use Alms\Bundle\CycleBundle\Schema\SchemaManager;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateModifiers;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderModifiers;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Spiral\Core\Container;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('cycle.schema.cache_dir', '');

    $parameters->set('cycle.schema.generator_classes', [
        TableInheritance::class,
        MergeColumns::class,
        GenerateRelations::class,
        RenderTables::class,
        GenerateModifiers::class,
        ValidateEntities::class,
        RenderTables::class,
        RenderRelations::class,
        RenderModifiers::class,
        MergeIndexes::class,
        GenerateTypecast::class,
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set('cycle.schema.loader.annotation', AnnotationLoader::class)
        ->arg('$locator', service('file_locator'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.closure', ClosureLoader::class)
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.container', ContainerLoader::class)
        ->arg('$container', service('service_container'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.directory', DirectoryLoader::class)
        ->arg('$locator', service('file_locator'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.glob', GlobFileLoader::class)
        ->arg('$locator', service('file_locator'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.psr4', Psr4DirectoryLoader::class)
        ->arg('$locator', service('file_locator'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.xml', XmlFileLoader::class)
        ->arg('$locator', service('file_locator'))
        ->arg('$inflector', service('cycle.core.inflector'))
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.loader.resolver', LoaderResolver::class)
        ->arg('$loaders', [
        ]);

    $services->set('cycle.schema.loader.delegating', DelegatingLoader::class)
        ->arg('$resolver', service('cycle.schema.loader.resolver'));

    $services->alias('cycle.schema.loader', 'cycle.schema.loader.delegating');

    $services->set('cycle.schema.loader.schema_resources', SchemaLoader::class)
        ->arg('$resources', '%cycle.schema.resources%')
        ->tag('cycle.schema.loader');

    $services->set('cycle.schema.compiler', Compiler::class);

    $services->set('cycle.schema.registry_factory', RegistryFactory::class)
        ->arg('$dbal', service('cycle.dbal.database_manager'));

    $services->set('cycle.schema.registry', Registry::class)
        ->arg('$dbal', service('cycle.dbal.database_manager'));

    $services->alias(Registry::class, 'cycle.schema.registry');

    $services->set('cycle.schema.generators', GeneratorCollection::class);

    $services->set('cycle.schema.generator.sync_tables', SyncTables::class);

    $services->set('cycle.schema.generator.apply_relation_defaults', ApplyRelationDefaults::class)
        ->arg('$inflector', service('cycle.core.inflector'))
        ->tag('cycle.schema.generator', [
            'priority' => 5,
        ]);

    $services->set('cycle.schema.schema_manager', SchemaManager::class)
        ->arg('$resource', '.')
        ->arg('$options', [
            'cache_dir' => '%cycle.schema.cache_dir%',
            'resource_type' => 'schema_resources',
            'debug' => '%kernel.debug%',
        ])
        ->tag('container.service_subscriber', [
            'key' => 'cycle.schema.loader',
            'id' => 'cycle.schema.loader',
        ])
        ->tag('container.service_subscriber', [
            'key' => 'cycle.schema.registry',
            'id' => 'cycle.schema.registry',
        ])
        ->tag('container.service_subscriber', [
            'key' => 'cycle.schema.compiler',
            'id' => 'cycle.schema.compiler',
        ])->tag('container.service_subscriber', [
            'key' => 'cycle.schema.generators',
            'id' => 'cycle.schema.generators',
        ]);

    $services->alias(SchemaManager::class, 'cycle.schema.schema_manager');

    $services->set('cycle.schema.schema', Schema::class)
        ->factory([
            service('cycle.schema.schema_manager'),
            'getSchema',
        ]);

    $services->alias(Schema::class, 'cycle.schema.schema');

    $services->set('cycle.schema.schema_to_array_converter', SchemaToArrayConverter::class);

    $services->alias(SchemaToArrayConverter::class, 'cycle.schema.schema_to_array_converter');

    $services->set('cycle.behavior.container', Container::class);

    $services->set('cycle.behavior.command_generator', EventDrivenCommandGenerator::class)
        ->arg('$schema', service('cycle.schema.schema'))
        ->arg('$container', service('cycle.behavior.container'));

    $services->set('cycle.orm.orm_factory', Factory::class)
        ->arg('$dbal', service('cycle.dbal.database_manager'));

    $services->set('cycle.orm.orm', ORM::class)
        ->arg('$factory', service('cycle.orm.orm_factory'))
        ->arg('$schema', service('cycle.schema.schema'))
        ->arg('$commandGenerator', service('cycle.behavior.command_generator'));

    $services->alias(ORM::class, 'cycle.orm.orm')->public();
    $services->alias(ORMInterface::class, 'cycle.orm.orm')->public();

    $services->set('cycle.orm.entity_manager', EntityManager::class)
        ->arg('$orm', service('cycle.orm.orm'));

    $services->alias(EntityManager::class, 'cycle.orm.entity_manager')->public();
    $services->alias(EntityManagerInterface::class, 'cycle.orm.entity_manager')->public();
};
