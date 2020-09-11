<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Analyzer\AnalyzerService;
use Bdf\Prime\Analyzer\Query\SqlQueryAnalyzer;
use Bdf\Prime\Query\Query;
use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

/**
 *
 */
class SqlQueryCasterTest extends PrimeShellTestCase
{
    /**
     * @var SqlQueryCaster
     */
    private $caster;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->caster = new SqlQueryCaster(new AnalyzerService([
            Query::class => new SqlQueryAnalyzer($this->prime)
        ]));
    }

    /**
     *
     */
    public function test_with_simple_query()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0',
            'entity' => TestEntity::class,
            'analysis' => ['Optimisation: use Bdf\Prime\Shell\_files\TestEntity::keyValue() instead'],
        ], ($this->caster)(TestEntity::builder()));
    }

    /**
     *
     */
    public function test_without_analysis_errors()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0 WHERE t0.id > 5',
            'entity' => TestEntity::class,
        ], ($this->caster)(TestEntity::where('id', '>', 5)));
    }

    /**
     *
     */
    public function test_with_complex_query()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0 INNER JOIN relation_entity t1 ON t1.id = t0.relation_id WHERE t0.id = 5 OR t1.name = \'Foo\'',
            'entity' => TestEntity::class,
            'analysis' => ['OR not nested on field "relation.name". Consider wrap the condition into a nested where : $query->where(function($query) { ... })'],
        ], ($this->caster)(TestEntity::builder()->where('id', 5)->orWhere('relation.name', 'Foo')));
    }

    /**
     *
     */
    public function test_by()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0',
            'entity' => TestEntity::class,
            'by' => [
                'attribute' => 'id',
                'combine' => false,
            ],
            'analysis' => ['Optimisation: use Bdf\Prime\Shell\_files\TestEntity::keyValue() instead'],
        ], ($this->caster)(TestEntity::by('id')));
    }

    /**
     *
     */
    public function test_with()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0',
            'entity' => TestEntity::class,
            'with' => [
                'relation' => [],
                'r2' => [],
            ],
            'analysis' => ['Optimisation: use Bdf\Prime\Shell\_files\TestEntity::keyValue() instead'],
        ], ($this->caster)(TestEntity::with(['relation', 'r2'])));
    }

    /**
     *
     */
    public function test_without()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0',
            'entity' => TestEntity::class,
            'without' => [
                'relation' => [],
            ],
            'analysis' => ['Optimisation: use Bdf\Prime\Shell\_files\TestEntity::keyValue() instead'],
        ], ($this->caster)(TestEntity::without('relation')));
    }
}
