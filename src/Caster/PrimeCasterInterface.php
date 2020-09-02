<?php

namespace Bdf\Prime\Shell\Caster;

/**
 * Class QueryCaster
 * @package Bdf\Prime\Shell\Caster
 */
interface PrimeCasterInterface
{
    /**
     * Get the type class name
     */
    public function type(): string;

    /**
     * Transform object for dumping on the shell
     *
     * @param object $object Object to dump
     * @return array Dump values
     */
    public function __invoke($object): array;
}
