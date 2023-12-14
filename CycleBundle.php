<?php

declare(strict_types=1);

/*
 * This file is part of the slince/cycle-bundle package.
 *
 * (c) Slince <taosikai@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alms\Bundle\CycleBundle;

use Alms\Bundle\CycleBundle\DependencyInjection\Compiler\OrmCompilerPass;
use Alms\Bundle\CycleBundle\Security\CycleUserProviderFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CycleBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new OrmCompilerPass());

        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $security */
            $security = $container->getExtension('security');
            $security->addUserProviderFactory(new CycleUserProviderFactory());
        }
    }

}