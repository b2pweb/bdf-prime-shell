<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Entity\Model;
use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;
use Psy\Context;

class QueryColumnMatcherTest extends PrimeShellTestCase
{
    /**
     * @var QueryColumnMatcher
     */
    private $matcher;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new QueryColumnMatcher($this->prime);
    }

    /**
     *
     */
    public function test_hasMatched()
    {
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where("')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where(\'')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where(\'foo')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where("foo')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where("foo", "bar")->inRows("')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::where("')));

        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where(')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->on("')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->cache("')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->first()')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->where')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->notFound("')));

        Model::configure(null);
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::where("')));
    }

    /**
     *
     */
    public function test_getMatches()
    {
        $this->assertEquals(['id"', 'value"', 'relation.id"', 'relation.name"'], $this->matcher->getMatches($this->tokens(TestEntity::class.'::builder()->where("')));
        $this->assertEquals(['id\'', 'value\'', 'relation.id\'', 'relation.name\''], $this->matcher->getMatches($this->tokens(TestEntity::class.'::builder()->where(\'')));
        $this->assertEquals(['relation.id"', 'relation.name"'], $this->matcher->getMatches($this->tokens(TestEntity::class.'::builder()->where("r')));
    }

    /**
     *
     */
    public function test_getMatches_with_variable()
    {
        $this->matcher->setContext($context = new Context());
        $context->setAll(['query' => TestEntity::builder()]);

        $this->assertEquals(['id"', 'value"', 'relation.id"', 'relation.name"'], $this->matcher->getMatches($this->tokens('$query->where("')));
        $this->assertEquals(['id\'', 'value\'', 'relation.id\'', 'relation.name\''], $this->matcher->getMatches($this->tokens('$query->where(\'')));
        $this->assertEquals(['relation.id"', 'relation.name"'], $this->matcher->getMatches($this->tokens('$query->where("r')));
    }
}
