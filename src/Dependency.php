<?php

namespace Sunder\DI;

use ReflectionException;

class Dependency
{
    private mixed $instance;

    public function __construct(
        private DI $di,
        private readonly mixed $data
    )
    {}

    /**
     * @throws ReflectionException
     */
    public function get(): mixed
    {
        if ($this->isFabric()) {
            return ($this->data)($this->di);
        }

        if (!isset($this->instance)) {
            $this->instance = $this->make();
        }

        return $this->instance;
    }

    /**
     * @throws ReflectionException
     */
    public function make(): mixed
    {
        if ($this->isFabric()) {
            return ($this->data)($this->di);
        }

        if ($this->isClassname()) {
            return $this->di->makeInstance($this->data);
        }

        return $this->data;
    }

    private function isFabric(): bool
    {
        return is_callable($this->data);
    }

    private function isClassname(): bool
    {
        return is_string($this->data) && class_exists($this->data);
    }
}