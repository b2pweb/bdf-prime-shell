<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Collection\Stream\StreamInterface;
use Bdf\Collection\Stream\Streams;
use ReflectionMethod;
use ReflectionObject;

/**
 * Utilities for create streams
 */
trait StreamTrait
{
    /**
     * Create a stream of objects methods
     *
     * @param object ...$objects
     *
     * @return StreamInterface
     */
    private function methodsStream(...$objects): StreamInterface
    {
        return Streams::wrap($objects)
            ->filter(function ($object) { return $object !== null; })
            ->flatMap(function ($object) { return (new ReflectionObject($object))->getMethods(ReflectionMethod::IS_PUBLIC); })
            ->filter(function (ReflectionMethod $method) { return substr($method->getName(), 0, 2) !== '__'; /* Ignore magic methods */ })
        ;
    }
}
