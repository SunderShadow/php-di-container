<?php

class DIContainerTest extends \PHPUnit\Framework\TestCase
{
    private \Sunder\DI\DI $dependencies;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->dependencies = new \Sunder\DI\DI([
            'alias' => 1,
            \Dependency\Foo::class => \Dependency\Foo::class,
            \Dependency\Bar::class => \Dependency\Bar::class
        ]);
    }

    public function test_isset()
    {
        $this->assertTrue($this->dependencies->isset('alias'));
        $this->assertFalse($this->dependencies->isset('notDefinedDependency'));
    }

    public function test_make()
    {
        $foo1 = $this->dependencies->make(\Dependency\Foo::class);
        $foo2 = $this->dependencies->make(\Dependency\Foo::class);

        $this->assertTrue($foo1 !== $foo2);
    }

    public function test_get_built_in_type()
    {
        $this->assertEquals(1, $this->dependencies->get('alias'));
    }

    public function test_get_object_from_classname()
    {
        $output = $this->dependencies->get(\Dependency\Foo::class);
        $this->assertIsObject($output);
    }

    public function test_get_object_from_classname_with_injection()
    {
        $output = $this->dependencies->get(\Dependency\Foo::class);
        $this->assertIsObject($output);
    }

    public function test_call_without_parameters()
    {
        $this->dependencies->call(function ($alias) {
            $this->assertEquals(1, $alias);
        });
    }

    public function test_call_with_parameters()
    {
        $this->dependencies->call(function ($alias, $customParam) {
            $this->assertEquals(1, $alias);
            $this->assertEquals('param', $customParam);
        }, [
            'customParam' => 'param'
        ]);
    }
}