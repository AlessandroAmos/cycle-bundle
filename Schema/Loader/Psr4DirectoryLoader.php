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

namespace Alms\Bundle\CycleBundle\Schema\Loader;

use Alms\Bundle\CycleBundle\Schema\GeneratorCollection;
use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\DirectoryAwareLoaderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\DirectoryResource;
use function is_array;

final class Psr4DirectoryLoader extends Loader implements DirectoryAwareLoaderInterface
{
    private ?string $currentDirectory = null;

    public function __construct(
        private readonly FileLocatorInterface $locator,
    )
    {
        // PSR-4 directory loader has no env-aware logic, so we drop the $env constructor parameter.
        parent::__construct();
    }

    /**
     * @param array{path: string, namespace: string} $resource
     */
    public function load(mixed $resource, string $type = null): ?GeneratorCollection
    {
        $path = $this->locator->locate($resource['path'], $this->currentDirectory);
        if (!is_dir($path)) {
            return new GeneratorCollection();
        }

        return $this->loadFromDirectory($path, trim($resource['namespace'], '\\'));
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return ('attribute' === $type || 'annotation' === $type) && is_array($resource) && isset($resource['path'], $resource['namespace']);
    }

    public function forDirectory(string $currentDirectory): static
    {
        $loader = clone $this;
        $loader->currentDirectory = $currentDirectory;

        return $loader;
    }

    private function loadFromDirectory(string $directory, string $psr4Prefix): GeneratorCollection
    {
        $collection = new GeneratorCollection();
        $collection->addResource(new DirectoryResource($directory, '/\.php$/'));
        $files = iterator_to_array(new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
                function (SplFileInfo $current) {
                    return !str_starts_with($current->getBasename(), '.');
                }
            ),
            RecursiveIteratorIterator::SELF_FIRST
        ));
        usort($files, function (SplFileInfo $a, SplFileInfo $b) {
            return (string)$a > (string)$b ? 1 : -1;
        });

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isDir()) {
                $collection->addCollection($this->loadFromDirectory($file->getPathname(), $psr4Prefix . '\\' . $file->getFilename()));

                continue;
            }
            if ('php' !== $file->getExtension() || !class_exists($className = $psr4Prefix . '\\' . $file->getBasename('.php')) || (new ReflectionClass($className))->isAbstract()) {
                continue;
            }

            $collection->addCollection($this->import($className, 'attribute'));
        }

        return $collection;
    }
}
