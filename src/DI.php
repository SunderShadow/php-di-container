<?php

namespace Sunder\DI;

use ReflectionException;

class DI
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

    public function isset(string $dependency): bool
    {
        return key_exists($dependency, $this->dependencies);
    }

    /**
     * @throws ReflectionException
     */
    public function get(string $dependency): mixed
    {
        return $this->dependencies[$dependency]->get();
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