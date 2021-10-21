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

        if (PHP_VERSION_ID < 80000) {
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
        } else {
            $this->assertSame($buffer, $buffer->forward());
            $this->assertEquals('<?php ', $buffer->asString());
            $this->assertEquals('Bdf\Prime\Shell\_files\TestEntity', $buffer->next()->asString());
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
    }

    /**
     *
     */
    public function test_cursor_endToStart()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        if (PHP_VERSION_ID < 80000) {
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
        } else {
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
            $this->assertEquals('Bdf\Prime\Shell\_files\TestEntity', $buffer->next()->asString());
            $this->assertEquals('<?php ', $buffer->next()->asString());
            $this->assertNull($buffer->next()->asString());
        }
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
    public function test_goTo()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));

        $this->assertEquals('where', $buffer->goTo(5)->asString());
    }

    /**
     *
     */
    public function test_get()
    {
        $buffer = new TokensBuffer($this->tokens('builder()->where("foo","bar")'));
        $buffer->next();

        $this->assertEquals([T_STRING, 'builder', 1], $buffer->get());
        $this->assertEquals('(', $buffer->get(1));
        $this->assertEquals([T_STRING, 'where', 1], $buffer->goTo(6)->get(-1));
    }

    /**
     *
     */
    public function test_is()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        if (PHP_VERSION_ID < 80000) {
            $this->assertTrue($buffer->next()->is(T_STRING));
            $this->assertFalse($buffer->is(T_DOUBLE_COLON));
            $this->assertFalse($buffer->reverse()->is(T_STRING));
            $this->assertTrue($buffer->is(T_CONSTANT_ENCAPSED_STRING, 1));
            $this->assertFalse($buffer->goTo(100)->is(T_STRING));
        } else {
            $this->assertTrue($buffer->next()->is(T_NAME_QUALIFIED));
            $this->assertFalse($buffer->is(T_DOUBLE_COLON));
            $this->assertFalse($buffer->reverse()->is(T_STRING));
            $this->assertTrue($buffer->is(T_CONSTANT_ENCAPSED_STRING, 1));
            $this->assertFalse($buffer->goTo(100)->is(T_NAME_QUALIFIED));
        }
    }

    /**
     *
     */
    public function test_equals()
    {
        $buffer = new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo","bar")'));

        $this->assertTrue($buffer->next()->equals(PHP_VERSION_ID < 80000 ? 'Bdf' : TestEntity::class));
        $this->assertFalse($buffer->next()->equals('aaa'));
        $this->assertTrue($buffer->reverse()->equals(')'));
        $this->assertTrue($buffer->equals(',', 2));
        $this->assertFalse($buffer->goTo(100)->equals(')'));
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

        $this->assertEquals(new TokensBuffer([]), $buffer->before());
        $this->assertEquals(new TokensBuffer($this->tokens('builder()->where("foo","bar"')), $buffer->reverse()->before());
        $this->assertEquals(new TokensBuffer($this->tokens('builder()')), $buffer->forward()->next(4)->before());
    }

    /**
     *
     */
    public function test_all()
    {
        $buffer = new TokensBuffer($this->tokens('builder()  ->  where  (  "foo"  ,  "bar"  )'));

        $this->assertSame([
            [T_OPEN_TAG, '<?php ', 1],
            [T_STRING, 'builder', 1],
            '(', ')',
            [T_OBJECT_OPERATOR, '->', 1],
            [T_STRING, 'where', 1],
            '(',
            [T_CONSTANT_ENCAPSED_STRING, '"foo"', 1],
            ',',
            [T_CONSTANT_ENCAPSED_STRING, '"bar"', 1],
            ')',
        ], $buffer->all());
    }
}
