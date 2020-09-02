<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;

class TokensBufferTest extends PrimeShellTestCase
{
    /**
     *
     */
    public function test_cursor_startToEnd()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertSame($buffer, $buffer->forward());
        $this->assertEquals('<?php ', $buffer->asString());
        $this->assertEquals('Bdf', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('Prime', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('Shell', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('_files', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('TestEntity', $buffer->next()->asString());
        $this->assertEquals('::', $buffer->next()->asString());
        $this->assertEquals('builder', $buffer->next()->asString());
        $this->assertEquals('(', $buffer->next()->asString());
        $this->assertEquals(')', $buffer->next()->asString());
        $this->assertEquals('->', $buffer->next()->asString());
        $this->assertEquals('where', $buffer->next()->asString());
        $this->assertEquals('(', $buffer->next()->asString());
        $this->assertEquals('"foo"', $buffer->next()->asString());
        $this->assertEquals(',', $buffer->next()->asString());
        $this->assertEquals('"bar"', $buffer->next()->asString());
        $this->assertEquals(')', $buffer->next()->asString());
        $this->assertNull($buffer->next()->asString());
    }

    /**
     *
     */
    public function test_cursor_endToStart()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertEquals(')', $buffer->reverse()->asString());
        $this->assertEquals('"bar"', $buffer->next()->asString());
        $this->assertEquals(',', $buffer->next()->asString());
        $this->assertEquals('"foo"', $buffer->next()->asString());
        $this->assertEquals('(', $buffer->next()->asString());
        $this->assertEquals('where', $buffer->next()->asString());
        $this->assertEquals('->', $buffer->next()->asString());
        $this->assertEquals(')', $buffer->next()->asString());
        $this->assertEquals('(', $buffer->next()->asString());
        $this->assertEquals('builder', $buffer->next()->asString());
        $this->assertEquals('::', $buffer->next()->asString());
        $this->assertEquals('TestEntity', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('_files', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('Shell', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('Prime', $buffer->next()->asString());
        $this->assertEquals('\\', $buffer->next()->asString());
        $this->assertEquals('Bdf', $buffer->next()->asString());
        $this->assertEquals('<?php ', $buffer->next()->asString());
        $this->assertNull($buffer->next()->asString());
    }

    /**
     *
     */
    public function test_next()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next(3)->equals(')'));
        $this->assertTrue($buffer->next(-2)->equals('builder'));
    }

    /**
     *
     */
    public function test_current()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));
        $buffer->next();

        $this->assertEquals([T_STRING, 'builder', 1], $buffer->current());
    }

    /**
     *
     */
    public function test_is()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next()->is(T_STRING));
        $this->assertFalse($buffer->is(T_DOUBLE_COLON));
        $this->assertFalse($buffer->reverse()->is(T_STRING));

        while ($buffer->current()) {
            $buffer->next();
        }

        $this->assertFalse($buffer->next()->is(T_STRING));
    }

    /**
     *
     */
    public function test_equals()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next()->equals('Bdf'));
        $this->assertFalse($buffer->next()->equals('aaa'));
        $this->assertTrue($buffer->reverse()->equals(')'));

        while ($buffer->current()) {
            $buffer->next();
        }

        $this->assertFalse($buffer->next()->equals(')'));
    }

    /**
     *
     */
    public function test_fullyQualifiedClassName()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertEquals(TestEntity::class, $buffer->next()->fullyQualifiedClassName());
        $this->assertTrue($buffer->is(T_DOUBLE_COLON));
        $this->assertEquals(TestEntity::class, $buffer->reverse()->next(11)->fullyQualifiedClassName());
        $this->assertTrue($buffer->is(T_OPEN_TAG));
    }

    /**
     *
     */
    public function test_matchInOrder()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next()->match(T_STRING, '(', ')', T_OBJECT_OPERATOR));
        $this->assertTrue($buffer->equals('where'));
        $this->assertFalse($buffer->next()->match('(', T_CONSTANT_ENCAPSED_STRING, ')'));
        $this->assertTrue($buffer->equals(','));
    }

    /**
     *
     */
    public function test_matchReverseOrder()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));
        $buffer->reverse();

        $this->assertTrue($buffer->match(')', T_CONSTANT_ENCAPSED_STRING, ',', T_CONSTANT_ENCAPSED_STRING, '('));
        $this->assertTrue($buffer->equals('where'));
        $this->assertFalse($buffer->next(2)->match(')', T_CONSTANT_ENCAPSED_STRING, '('));
        $this->assertTrue($buffer->equals('('));
    }

    /**
     *
     */
    public function test_before()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertEquals([], $buffer->before());
        $this->assertEquals($this->tokens('builder()->where("foo","bar"'), $buffer->reverse()->before());
        $this->assertEquals($this->tokens('builder()'), $buffer->forward()->next(4)->before());
    }
}