<?php

namespace Bdf\Prime\Shell;

use Bdf\Prime\ServiceLocator;
use Bdf\Prime\Shell\Caster\PrimeCasters;
use Bdf\Prime\Shell\Matcher\ModelMatcher;
use Bdf\Prime\Shell\Matcher\QueryColumnMatcher;
use Bdf\Prime\Shell\Matcher\QueryMatcher;
use Psy\Configuration;
use Psy\Shell;

/**
 * Create and configure the PsySH shell
 */
class ShellFactory
{
    /**
     * @var ServiceLocator
     */
    private $locator;

    /**
     * ShellFactory constructor.
     *
     * @param ServiceLocator $locator
     */
    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Instantiate the shell
     *
     * @return Shell
     */
    public function create(): Shell
    {
        $config = new Configuration();

        $config->useTabCompletion();
        $config->addMatchers([new ModelMatcher(), new QueryMatcher(), new QueryColumnMatcher()]);
        $config->addCasters((new PrimeCasters($this->locator))->all());

        return new Shell($config);
    }
}
