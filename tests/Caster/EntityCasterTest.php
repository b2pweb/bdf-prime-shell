<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

class EntityCasterTest extends PrimeShellTestCase
{
    /**
     * @var EntityCaster
     */
    private $caster;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caster = new EntityCaster($this->prime);
    }

    /**
     *
     */
    public function test_cast()
    {
        $this->assertEquals(
            [
                'id' => 5,
                'value' => 'test'
            ],
            ($this->caster)(new TestEntity([
                'id' => 5,
                'value' => 'test'
            ]))
        );
    }
}
