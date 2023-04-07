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
    public function make(string $dependency): mixed
    {
        return $this->dependencies[$dependency]->make();
    }

    /**
     * @template T
     * @param class-string<T> $outerClass
     * @return T
     * @throws ReflectionException
     */
    public function makeInstance(string $outerClass): mixed
    {
        if (!$constructor = (new \ReflectionClass($outerClass))->getConstructor()) {
            return new $outerClass;
        }

        $parameters = $this->resolveParameters($constructor->getParameters());

        return new $outerClass(...$parameters);
    }

    /**
     * Call function with auto injection
     * @param callable $cb
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     */
    public function call(callable $cb, array $parameters = []): mixed
    {
        $reflection = new \ReflectionFunction($cb);

        if (!$reflection->getParameters()) {
            return $cb();
        }

        $parametersToResolve = [];
        foreach ($reflection->getParameters() as $parameter) {
            if (!in_array($parameter->getName(), array_keys($parameters))) {
                $parametersToResolve[] = $parameter;
            }
        }

        $resolvedParameters  = array_merge($this->resolveParameters($parametersToResolve), $parameters);

        return $cb(...$resolvedParameters);
    }

    /**
     * Array of parameters
     * @param array $parameters
     * @return array
     * @throws ReflectionException
     */
    private function resolveParameters(array $parameters): array
    {
        $resolvedParameters = [];

        foreach ($parameters as $parameter) {
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