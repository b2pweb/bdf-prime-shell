<?php

namespace Bdf\Prime\Shell\Caster;

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

        $this->caster = new SqlQueryCaster();
    }

    /**
     *
     */
    public function test_with_simple_query()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0',
            'entity' => TestEntity::class,
        ], ($this->caster)(TestEntity::builder()));
    }

    /**
     *
     */
    public function test_with_complex_query()
    {
        $this->assertEquals([
            'SQL' => 'SELECT t0.* FROM test_entity t0 INNER JOIN relation_entity t1 ON t1.id = t0.relation_id WHERE t0.id = 5 OR t1.name = \'Foo\'',
            'entity' => TestEntity::class,
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
            ],
        ], ($this->caster)(TestEntity::with('relation')));
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
        ], ($this->caster)(TestEntity::without('relation')));
    }
}
