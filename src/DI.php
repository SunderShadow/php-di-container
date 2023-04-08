<?php

namespace Sunder\DI;

use Psr\Container\ContainerInterface;
use ReflectionException;

class DI implements ContainerInterface
{
    /**
     * @var Dependency[]
     */
    private array $dependencies;

    public function __construct(array $dependencies)
    {
        foreach ($dependencies as $alias => $dependency) {
            $this->dependencies[$alias] = new Dependency($this, $dependency);
        }
    }

    public function has(string $id): bool
    {
        return key_exists($id, $this->dependencies);
    }

    /**
     * @throws ReflectionException
     */
    public function get(string $id): mixed
    {
        return $this->dependencies[$id]->get();
    }

    /**
     * @throws ReflectionException
     */
    public function make(string $id): mixed
    {
        return $this->dependencies[$id]->make();
    }

    /**
     * @template T
     * @param class-string<T> $outerClassname
     * @return T
     * @throws ReflectionException
     */
    public function makeInstance(string $outerClassname): mixed
    {
        if (!$constructor = (new \ReflectionClass($outerClassname))->getConstructor()) {
            return new $outerClassname;
        }

        $parameters = $this->resolveParameters($constructor->getParameters());

        return new $outerClassname(...$parameters);
    }

    /**
     * Call function with auto injection
     * @param callable $cb
     * @param array $defaultParameters
     * @return mixed
     * @throws ReflectionException
     */
    public function call(callable $cb, array $defaultParameters = []): mixed
    {
        $reflection = new \ReflectionFunction($cb);

        if (!$reflection->getParameters()) {
            return $cb();
        }

        $parametersToResolve = [];
        foreach ($reflection->getParameters() as $parameter) {
            if (!in_array($parameter->getName(), array_keys($defaultParameters))) {
                $parametersToResolve[] = $parameter;
            }
        }

        $resolvedParameters  = array_merge($this->resolveParameters($parametersToResolve), $defaultParameters);

        return $cb(...$resolvedParameters);
    }

    /**
     * Array of parameters
     * @param array $parametersToResolve
     * @return array
     * @throws ReflectionException
     */
    private function resolveParameters(array $parametersToResolve): array
    {
        $resolvedParameters = [];

        foreach ($parametersToResolve as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (!$type || $type->isBuiltin()) {
                $type = $name;
            }

            $resolvedParameters[$name] = $this->get($type);
        }

        return $resolvedParameters;
    }
}