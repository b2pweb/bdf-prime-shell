<?php

namespace Bdf\Prime\Shell;

use Bdf\Prime\Analyzer\AnalyzerService;
use Bdf\Prime\Analyzer\BulkInsertQuery\BulkInsertQueryAnalyzer;
use Bdf\Prime\Analyzer\KeyValueQuery\KeyValueQueryAnalyzer;
use Bdf\Prime\Analyzer\Query\SqlQueryAnalyzer;
use Bdf\Prime\Query\Custom\BulkInsert\BulkInsertQuery;
use Bdf\Prime\Query\Custom\KeyValue\KeyValueQuery;
use Bdf\Prime\Query\Query;
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
        $config->addMatchers([new ModelMatcher(), new QueryMatcher($this->locator), new QueryColumnMatcher($this->locator)]);
        /** @psalm-suppress InvalidArgument */
        // psalm class-string-map does not works
        $config->addCasters(
            (new PrimeCasters($this->locator, new AnalyzerService([
                Query::class => new SqlQueryAnalyzer($this->locator),
                KeyValueQuery::class => new KeyValueQueryAnalyzer($this->locator),
                BulkInsertQuery::class => new BulkInsertQueryAnalyzer($this->locator)
            ])))->all()
        );

        return new Shell($config);
    }
}
