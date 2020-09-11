<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Entity\Model;
use Bdf\Prime\Shell\_files\RelationEntity;
use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

/**
 *
 */
class ModelMatcherTest extends PrimeShellTestCase
{
    /**
     * @var ModelMatcher
     */
    private $matcher;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->matcher = new ModelMatcher();
    }

    /**
     *
     */
    public function test_hasMatched()
    {
        $this->assertFalse($this->matcher->hasMatched($this->tokens('$var')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens('InvalidClass::')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class)));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::whe')));

        Model::configure(null);
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::whe')));
    }

    /**
     *
     */
    public function test_getMatches()
    {
        $matches = $this->matcher->getMatches($this->tokens(TestEntity::class.'::'));
        $this->assertSame(range(0, count($matches) - 1), array_keys($matches));

        $this->assertContains('TestEntity::where(', $matches);
        $this->assertContains('TestEntity::orWhere(', $matches);
        $this->assertContains('TestEntity::all()', $matches);
        $this->assertContains('TestEntity::builder()', $matches);
        $this->assertContains('TestEntity::findById(', $matches);
        $this->assertContains('TestEntity::keyValue()', $matches);
        $this->assertContains('TestEntity::connection()', $matches);
        $this->assertContains('TestEntity::count()', $matches);
        $this->assertContains('TestEntity::loaded(', $matches);
        $this->assertContains('TestEntity::myScope(', $matches);
        $this->assertContains('TestEntity::myQuery(', $matches);
        $this->assertNotContains('TestEntity::__construct(', $matches);
        $this->assertNotContains('TestEntity::__toString()', $matches);

        $this->assertContains('RelationEntity::keyValue()', $this->matcher->getMatches($this->tokens(RelationEntity::class.'::')));
    }

    /**
     *
     */
    public function test_getMatches_with_filter()
    {
        $this->assertEqualsCanonicalizing([
            'TestEntity::where(',
            'TestEntity::whereNull(',
            'TestEntity::whereNotNull(',
            'TestEntity::whereRaw(',
        ], $this->matcher->getMatches($this->tokens(TestEntity::class.'::whe')));

        $this->assertEqualsCanonicalizing([
            'TestEntity::myScope(',
            'TestEntity::myQuery(',
        ], $this->matcher->getMatches($this->tokens(TestEntity::class.'::my')));

        $this->assertEmpty($this->matcher->getMatches($this->tokens(TestEntity::class.'::notFound')));
    }
}
