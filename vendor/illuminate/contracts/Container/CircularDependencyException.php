<?php

namespace Behastan202511\Illuminate\Contracts\Container;

use Exception;
use Behastan202511\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
