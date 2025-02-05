<?php

declare(strict_types=1);

namespace Behastan\Tests;

use Behastan\DependencyInjection\ContainerFactory;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $containerFactory = new ContainerFactory();
        $this->container = $containerFactory->create();
    }

    /**
     * @template TType of object
     * @param class-string<TType> $type
     * @return TType
     */
    protected function make(string $type): object
    {
        return $this->container->make($type);
    }
}
