<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Prime\Shell\_files\RelationEntity;
use Bdf\Prime\Shell\_files\TestEntity;
use Bdf\Prime\Shell\PrimeShellTestCase;
use Doctrine\DBAL\Logging\DebugStack;
use Psy\Context;

/**
 *
 */
class QueryResolverTest extends PrimeShellTestCase
{
    /**
     * @var DebugStack
     */
    private $logger;

    protected function setUp()
    {
        parent::setUp();

        $this->prime->connection('test')->getConfiguration()->setSQLLogger($this->logger = new DebugStack());
    }

    /**
     *
     */
    public function test_invalid_queries()
    {
        $resolver = new QueryResolver($this->prime);

        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('InvalidClass::builder()->where("foo", "bar")'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::class'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::invalid'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::invalid()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::repository()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::repository()->invalid()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::connection()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->execute()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->first()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->all()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->inRows()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->inRow()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->update()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->insert()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->replace()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->find([])'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->findOne([])'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->count()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->avg()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->sum()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->max()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->min()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->aggregate("max")'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->cache()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->invalid()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where(syntax !! error %%)'))));

        $this->assertEmpty($this->logger->queries);
    }

    /**
     *
     */
    public function test_dangerous_query_not_executed()
    {
        $entity = (new TestEntity())->setValue('foo');
        TestEntity::repository()->schema()->migrate();
        $entity->insert();

        $resolver = new QueryResolver($this->prime);
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::dangerousQuery()'))));

        $this->assertEquals($entity, TestEntity::refresh($entity));
    }

    /**
     *
     */
    public function test_resolve_valid_query()
    {
        $resolver = new QueryResolver($this->prime);

        $this->assertEquals('__tmp', $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()')))->connection()->getName());
        $this->assertNotContains('__tmp', $this->prime->connections()->getCurrentConnectionNames());

        $this->assertEquals(
            TestEntity::builder()->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()')))->statements
        );


        $this->assertEquals(
            RelationEntity::builder()->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(RelationEntity::class.'::builder()')))->statements
        );

        $this->assertEquals(
            TestEntity::builder()->statements,
            $resolver->resolve((new TokensBuffer($this->tokens(TestEntity::class.'::builder()')))->reverse())->statements
        );

        $this->assertEquals(
            TestEntity::where('foo', 'bar')->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::where("foo", "bar")')))->statements
        );

        $this->assertEquals(
            TestEntity::where('foo', 'bar')->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::repository()->where("foo", "bar")')))->statements
        );

        $this->assertEquals(
            TestEntity::where('foo', 'bar')->orWhere("aaa", "bbb")->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::repository()->where("foo", "bar")->orWhere("aaa", "bbb")')))->statements
        );

        $this->assertEquals(
            TestEntity::distinct()->where('foo', 'bar')->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::distinct()->where("foo", "bar")')))->statements
        );

        $this->assertEquals(
            TestEntity::where('value', ':like', '%foo%')->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::myScope("foo")')))->statements
        );

        $this->assertEquals(
            TestEntity::keyValue('value', 'foo')->statements,
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::myQuery("foo")')))->statements
        );

        $this->assertEmpty($this->logger->queries);
    }

    /**
     *
     */
    public function test_query_cache()
    {
        $resolver = new QueryResolver($this->prime);

        $this->assertSame(
            $query = $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()'))),
            $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()')))
        );

        $this->assertNotEquals($query, $resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->where("foo", "bar")'))));
    }

    /**
     *
     */
    public function test_resolve_with_variable()
    {
        $context = new Context();
        $resolver = new QueryResolver($this->prime);
        $resolver->setContext($context);

        $context->setAll(['query' => $query = TestEntity::builder()]);
        $this->assertEquals("SELECT t0.* FROM test_entity t0 WHERE foo = 'bar'", $resolver->resolve(new TokensBuffer($this->tokens('$query->where("foo", "bar")')))->toRawSql());
        $this->assertNotSame($query, $resolver->resolve(new TokensBuffer($this->tokens('$query->where("foo", "bar")'))));

        $context->setAll(['repo' => TestEntity::repository()]);
        $this->assertEquals("SELECT t0.* FROM test_entity t0 WHERE foo = 'bar'", $resolver->resolve(new TokensBuffer($this->tokens('$repo->where("foo", "bar")')))->toRawSql());
    }

    /**
     *
     */
    public function test_resolve_fail_with_variable()
    {
        $context = new Context();
        $resolver = new QueryResolver($this->prime);
        $resolver->setContext($context);

        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('$query->where("foo","bar")'))));

        $context->setAll([
            'query' => TestEntity::builder(),
            'repo' => TestEntity::repository(),
        ]);
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('$notFound->where("foo","bar")'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('$repo->connection()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('$repo->invalid()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens('$query->where("foo","bar")->first()'))));
        $this->assertNull($resolver->resolve(new TokensBuffer($this->tokens(TestEntity::class.'::builder()->first()'))));
    }
}
