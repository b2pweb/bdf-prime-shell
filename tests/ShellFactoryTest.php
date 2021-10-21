<?php

namespace Bdf\Prime\Shell;

use Psy\Shell;

/**
 * Class ShellFactoryTest
 * @package Bdf\Prime\Shell
 */
class ShellFactoryTest extends PrimeShellTestCase
{
    /**
     * @var ShellFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ShellFactory($this->prime);
    }

    /**
     *
     */
    public function test_create()
    {
        $this->assertInstanceOf(Shell::class, $this->factory->create());
    }
}
