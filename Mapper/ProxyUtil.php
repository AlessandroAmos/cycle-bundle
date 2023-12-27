<?php

namespace Alms\Bundle\CycleBundle\Mapper;

use Symfony\Component\HttpKernel\KernelInterface;

class ProxyUtil
{
    protected array $proxies = [];

    public function __construct(
        protected KernelInterface $kernel
    ) {}

    public function getProxyCacheFolderDir(): string
    {
        return $this->kernel->getCacheDir() . '/cycle/proxies';
    }

    public function saveProxy(string $className, string $proxy): void
    {
        $dir = $this->getProxyCacheFolderDir();

        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . $className . '.php';

        file_put_contents($file, $proxy);
    }

    public function requireProxy(string $className): void
    {
        $dir = $this->getProxyCacheFolderDir();

        if (!is_dir($dir))
        {
            return;
        }

        $file = $dir . '/' . $className . '.php';

        require_once $file;
    }

    public function proxyExists(string $className): bool
    {
        $dir = $this->getProxyCacheFolderDir();

        if (!is_dir($dir))
        {
            return false;
        }

        $file = $dir . '/' . $className . '.php';

        return file_exists($file);
    }

    public function preload(): void
    {
        $dir = $this->getProxyCacheFolderDir();

        if (!is_dir($dir))
        {
            return;
        }

        $files = scandir($dir);

        foreach ($files as $file)
        {
            if (in_array($file, ['.', '..']))
            {
                continue;
            }

            require_once $dir . '/' . $file;
        }
    }
}