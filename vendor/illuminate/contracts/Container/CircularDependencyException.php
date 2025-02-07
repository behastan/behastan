<?php

namespace Behastan202502\Illuminate\Contracts\Container;

use Exception;
use Behastan202502\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
