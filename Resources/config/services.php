<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import(__DIR__ . '/./dbal.php');
    $container->import(__DIR__ . '/./orm.php');
    $container->import(__DIR__ . '/./migration.php');
    $container->import(__DIR__ . '/./tools.php');
};
