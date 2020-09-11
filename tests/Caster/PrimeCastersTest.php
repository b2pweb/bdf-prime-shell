<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Analyzer\AnalyzerService;
use Bdf\Prime\Entity\EntityInterface;
use Bdf\Prime\Query\Custom\KeyValue\KeyValueQuery;
use Bdf\Prime\Query\SqlQueryInterface;
use Bdf\Prime\Shell\PrimeShellTestCase;

/**
 * Class PrimeCastersTest
 * @package Bdf\Prime\Shell\Caster
 */
class PrimeCastersTest extends PrimeShellTestCase
{
    /**
     * @var PrimeCasters
     */
    private $casters;

    protected function setUp()
    {
        parent::setUp();

        $this->casters = new PrimeCasters($this->prime, new AnalyzerService([]));
    }

    /**
     *
     */
    public function test_all()
    {
        $casters = $this->casters->all();

        $this->assertEqualsCanonicalizing([EntityInterface::class, SqlQueryInterface::class, KeyValueQuery::class], array_keys($casters));
        $this->assertInstanceOf(SqlQueryCaster::class, $casters[SqlQueryInterface::class]);
        $this->assertInstanceOf(EntityCaster::class, $casters[EntityInterface::class]);
        $this->assertInstanceOf(KeyValueQueryCaster::class, $casters[KeyValueQuery::class]);
    }

    /**
     *
     */
    public function test_register()
    {
        $caster = new class implements PrimeCasterInterface {
            public function type(): string { return 'Foo'; }
            public function __invoke($object): array { return []; }
        };

        $this->casters->register($caster);

        $this->assertSame($caster, $this->casters->all()['Foo']);
    }
}
