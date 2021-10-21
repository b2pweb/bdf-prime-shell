<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Analyzer\AnalyzerService;
use Bdf\Prime\Analyzer\KeyValueQuery\KeyValueQueryAnalyzer;
use Bdf\Prime\Analyzer\Query\SqlQueryAnalyzer;
use Bdf\Prime\Prime;
use Bdf\Prime\Query\Custom\KeyValue\KeyValueQuery;
use Bdf\Prime\Query\Query;
use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

/**
 *
 */
class KeyValueQueryCasterTest extends PrimeShellTestCase
{
    /**
     * @var KeyValueQueryCaster
     */
    private $caster;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        Prime::create(TestEntity::class);

        $this->caster = new KeyValueQueryCaster(new AnalyzerService([
            KeyValueQuery::class => new KeyValueQueryAnalyzer($this->prime)
        ]));
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        Prime::drop(TestEntity::class);
        parent::tearDown();
    }

    /**
     *
     */
    public function test_with_simple_query()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity',
            'entity' => TestEntity::class,
        ], ($this->caster)(TestEntity::keyValue()));
    }

    /**
     *
     */
    public function test_with_analysis_error()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity WHERE relation_id = ?',
            'bindings' => ['bar'],
            'entity' => TestEntity::class,
            'analysis' => ['Use of undeclared attribute "relation_id".', 'Query without index. Consider adding an index, or filter on an indexed field.'],
        ], ($this->caster)(TestEntity::keyValue('relation_id', 'bar')));
    }

    /**
     *
     */
    public function test_with_bindings()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity WHERE id = ? AND value = ?',
            'bindings' => [5, 'Foo'],
            'entity' => TestEntity::class,
        ], ($this->caster)(TestEntity::keyValue()->where('id', 5)->where('value', 'Foo')));
    }

    /**
     *
     */
    public function test_by()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity',
            'entity' => TestEntity::class,
            'by' => [
                'attribute' => 'id',
                'combine' => false,
            ],
        ], ($this->caster)(TestEntity::keyValue()->by('id')));
    }

    /**
     *
     */
    public function test_with()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity',
            'entity' => TestEntity::class,
            'with' => [
                'relation' => [],
            ],
        ], ($this->caster)(TestEntity::keyValue()->with('relation')));
    }

    /**
     *
     */
    public function test_without()
    {
        $this->assertEquals([
            'SQL' => 'SELECT * FROM test_entity',
            'entity' => TestEntity::class,
            'without' => [
                'relation' => [],
            ],
        ], ($this->caster)(TestEntity::keyValue()->without('relation')));
    }
}
