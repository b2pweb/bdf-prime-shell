<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Analyzer\AnalyzerService;
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
     *
     * @param ServiceLocator $prime
     * @param AnalyzerService $analyzerService
     */
    public function __construct(ServiceLocator $prime, AnalyzerService $analyzerService)
    {
        $this->prime = $prime;

        $this->register(new SqlQueryCaster($analyzerService));
        $this->register(new KeyValueQueryCaster($analyzerService));
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
