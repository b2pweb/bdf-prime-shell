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
        $buffer->toStart();

        $this->assertSame($buffer, $buffer->toStart());
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

        $this->assertEquals(')', $buffer->toEnd()->asString());
        $this->assertEquals('"bar"', $buffer->previous()->asString());
        $this->assertEquals(',', $buffer->previous()->asString());
        $this->assertEquals('"foo"', $buffer->previous()->asString());
        $this->assertEquals('(', $buffer->previous()->asString());
        $this->assertEquals('where', $buffer->previous()->asString());
        $this->assertEquals('->', $buffer->previous()->asString());
        $this->assertEquals(')', $buffer->previous()->asString());
        $this->assertEquals('(', $buffer->previous()->asString());
        $this->assertEquals('builder', $buffer->previous()->asString());
        $this->assertEquals('::', $buffer->previous()->asString());
        $this->assertEquals('TestEntity', $buffer->previous()->asString());
        $this->assertEquals('\\', $buffer->previous()->asString());
        $this->assertEquals('_files', $buffer->previous()->asString());
        $this->assertEquals('\\', $buffer->previous()->asString());
        $this->assertEquals('Shell', $buffer->previous()->asString());
        $this->assertEquals('\\', $buffer->previous()->asString());
        $this->assertEquals('Prime', $buffer->previous()->asString());
        $this->assertEquals('\\', $buffer->previous()->asString());
        $this->assertEquals('Bdf', $buffer->previous()->asString());
        $this->assertEquals('<?php ', $buffer->previous()->asString());
        $this->assertNull($buffer->previous()->asString());
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
        $this->assertFalse($buffer->toEnd()->is(T_STRING));
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
        $this->assertTrue($buffer->toEnd()->equals(')'));
        $this->assertFalse($buffer->next()->equals(')'));
    }

    /**
     *
     */
    public function test_fullyQualifiedClassName()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertEquals(TestEntity::class, $buffer->next()->fullyQualifiedClassName(true));
        $this->assertTrue($buffer->is(T_DOUBLE_COLON));
        $this->assertEquals(TestEntity::class, $buffer->previous()->fullyQualifiedClassName(false));
        $this->assertTrue($buffer->is(T_OPEN_TAG));
    }

    /**
     *
     */
    public function test_matchInOrder()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next()->matchInOrder(T_STRING, '(', ')', T_OBJECT_OPERATOR));
        $this->assertTrue($buffer->equals('where'));
        $this->assertFalse($buffer->next()->matchInOrder('(', T_CONSTANT_ENCAPSED_STRING, ')'));
        $this->assertTrue($buffer->equals(','));
    }

    /**
     *
     */
    public function test_matchReverseOrder()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));
        $buffer->toEnd();

        $this->assertTrue($buffer->matchReverseOrder(')', T_CONSTANT_ENCAPSED_STRING, ',', T_CONSTANT_ENCAPSED_STRING, '('));
        $this->assertTrue($buffer->equals('where'));
        $this->assertFalse($buffer->previous()->previous()->matchReverseOrder(')', T_CONSTANT_ENCAPSED_STRING, '('));
        $this->assertTrue($buffer->equals('('));
    }

    /**
     *
     */
    public function test_before()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertEquals([], $buffer->before());
        $this->assertEquals($this->tokens('builder()->where("foo","bar"'), $buffer->toEnd()->before());
        $this->assertEquals($this->tokens('builder()'), $buffer->toStart()->next()->next()->next()->next()->before());
    }
}
