<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Collection\Stream\Streams;
use Bdf\Prime\ServiceLocator;
use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;
use Bdf\Prime\Shell\Util\QueryResolver;
use Bdf\Prime\Shell\Util\StreamTrait;
use Bdf\Prime\Shell\Util\TokensBuffer;
use Psy\Context;
use Psy\ContextAware;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionMethod;
use function array_pop;

/**
 * Add query method autocomplete
 * Handle method chaining
 */
final class QueryMatcher extends AbstractMatcher implements ContextAware
{
    use QueryExtensionGetterTrait;
    use StreamTrait;

    /**
     * @var QueryResolver
     */
    private $resolver;

    /**
     * QueryMatcher constructor.
     *
     * @param ServiceLocator $prime
     */
    public function __construct(ServiceLocator $prime)
    {
        $this->resolver = new QueryResolver($prime);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(Context $context): void
    {
        $this->resolver->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatches(array $tokens, array $info = []): array
    {
        $input = '';

        if (self::tokenIs(($token = array_pop($tokens)), self::T_STRING)) {
            array_pop($tokens);
            $input = $token[1];
        }

        $query = $this->resolver->resolve(new TokensBuffer($tokens));

        $stream = $this->methodsStream($query, $this->getExtension($query))
            ->filter(function (\ReflectionMethod $method) use($input) {
                return self::startsWith($input, $method->getName());
            })
            ->map(function (ReflectionMethod $method) {
                return $method->getName().'('.($method->getNumberOfRequiredParameters() > 0 ? '' : ')');
            })
        ;

        try {
            if ($repository = $this->getExtensionRepository($query)) {
                $scopes = Streams::wrap(array_keys($repository->mapper()->scopes()))
                    ->filter(function (string $name) use ($input) { return self::startsWith($input, $name); })
                    ->map(function (string $name) { return $name . '('; })
                ;

                $stream = $stream->concat($scopes);
            }
        } catch (\LogicException $e) {
            // No scopes defined : Ignore
        }

        return $stream
            ->distinct()
            ->toArray(false)
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @return bool
     */
    public function hasMatched(array $tokens): bool
    {
        $buffer = new TokensBuffer($tokens);
        $buffer->reverse();

        // query()->xxx
        if ($buffer->is(T_STRING)) {
            $buffer->next();
        }

        // query()->
        if (!$buffer->is(T_OBJECT_OPERATOR)) {
            return false;
        }

        return $this->resolver->resolve($buffer->before()) !== null;
    }
}
