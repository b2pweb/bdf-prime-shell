<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\ServiceLocator;

/**
 * Store all prime casters
 */
class PrimeCasters
{
    /**
     * @var ServiceLocator
     */
    private $prime;

    /**
     * @var PrimeCasterInterface[]
     */
    private $casters = [];


    /**
     * PrimeCasters constructor.
     * @param ServiceLocator $prime
     */
    public function __construct(ServiceLocator $prime)
    {
        $this->prime = $prime;

        $this->register(new SqlQueryCaster());
        $this->register(new EntityCaster($prime));
    }

    /**
     * Register a new caster
     *
     * @param PrimeCasterInterface $caster
     */
    public function register(PrimeCasterInterface $caster): void
    {
        $this->casters[$caster->type()] = $caster;
    }

    /**
     * Get all casters
     *
     * @return callable[]
     */
    public function all(): array
    {
        return $this->casters;
    }
}
