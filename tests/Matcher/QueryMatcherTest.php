<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

/**
 *
 */
class QueryMatcherTest extends PrimeShellTestCase
{
    /**
     * @var QueryMatcher
     */
    private $matcher;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->matcher = new QueryMatcher();
    }

    /**
     *
     */
    public function test_hasMatched()
    {
        $this->assertFalse($this->matcher->hasMatched($this->tokens('$var')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens('NotModel')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::wh')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::connection()')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::connection()->')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::where("id", 5)->')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::where("id", 5)->orWhere("value", "foo")->')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::repository()->where("id", 5)->')));
        $this->assertTrue($this->matcher->hasMatched($this->tokens(TestEntity::class.'::repository()->where("id", 5)->wh')));

        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->first()->')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->all()[0]->')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->insert()')));
        $this->assertFalse($this->matcher->hasMatched($this->tokens(TestEntity::class.'::builder()->findOne(')));
    }

    /**
     *
     */
    public function test_getMatches()
    {
        $matches = $this->matcher->getMatches($this->tokens(TestEntity::class.'::where("id", 5)->'));

        $this->assertSame(range(0, count($matches) - 1), array_keys($matches));

        $this->assertContains('where(', $matches);
        $this->assertContains('orWhere(', $matches);
        $this->assertContains('first()', $matches);
        $this->assertContains('all()', $matches);
        $this->assertContains('toSql()', $matches);
        $this->assertContains('count()', $matches);
        $this->assertContains('by(', $matches);

        $this->assertNotContains('__toString()', $matches);
    }

    /**
     *
     */
    public function test_getMatches_with_filter()
    {
        $this->assertEqualsCanonicalizing([
            'orHaving(', 'orHavingNull(', 'orHavingNotNull(', 'orHavingRaw(',
            'orWhere(', 'orWhereNotNull(', 'orWhereNull(', 'orWhereRaw(',
            'order(',
        ], $this->matcher->getMatches($this->tokens(TestEntity::class.'::where("id", 5)->or')));
    }
}
