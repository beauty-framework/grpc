<?php
declare(strict_types=1);

namespace Beauty\GRPC;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\RoadRunner\GRPC\Server;
use Symfony\Component\Finder\Finder;

class GrpcServiceRegistry
{
    /**
     * @param ContainerInterface $container
     * @param Server $server
     */
    public function __construct(
        private ContainerInterface $container,
        private Server $server,
    )
    {
    }

    /**
     * @param array $globs
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function registerFromAttributes(array $globs): void
    {
        $loadedBefore = get_declared_classes();

        $finder = new Finder();
        foreach ($globs as $globPath) {
            $dir = dirname($globPath);
            $pattern = basename($globPath);

            $finder->files()
                ->in(dirname($dir))
                ->name($pattern)
                ->depth('>= 0');
        }

        foreach ($finder as $file) {
            require_once $file->getRealPath();
        }

        $loadedNow = array_diff(get_declared_classes(), $loadedBefore);

        foreach ($loadedNow as $class) {
            $ref = new ReflectionClass($class);

            if (! $ref->isInstantiable() || ! $ref->isUserDefined()) {
                continue;
            }

            $attribute = $ref->getAttributes(GrpcService::class)[0] ?? null;

            if (! $attribute) {
                continue;
            }

            $definition = $attribute->newInstance();

            $this->server->registerService(
                $definition->name,
                $this->container->get($class)
            );
        }
    }
}