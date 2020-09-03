<?php

namespace Bdf\Prime\Shell\Caster;

/**
 * @template T
 */
interface PrimeCasterInterface
{
    /**
     * Get the type class name
     *
     * @return class-string<T>
     */
    public function type(): string;

    /**
     * Transform object for dumping on the shell
     *
     * @param T $object Object to dump
     *
     * @return array Dump values
     */
    public function __invoke($object): array;
}
