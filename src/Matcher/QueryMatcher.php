<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;
use Bdf\Prime\Shell\Util\QueryResolver;
use Bdf\Prime\Shell\Util\StreamTrait;
use Bdf\Prime\Shell\Util\TokensBuffer;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionMethod;
use function array_pop;

/**
 * Add query method autocomplete
 * Handle method chaining
 */
final class QueryMatcher extends AbstractMatcher
{
    use QueryExtensionGetterTrait;
    use StreamTrait;

    /**
     * {@inheritdoc}
     */
    public function getMatches(array $tokens, array $info = [])
    {
        $input = '';

        if (self::tokenIs(($token = array_pop($tokens)), self::T_STRING)) {
            array_pop($tokens);
            $input = $token[1];
        }

        $query = (new QueryResolver())->resolve(new TokensBuffer($tokens));

        return $this->methodsStream($query, $this->getExtension($query))
            ->filter(function (\ReflectionMethod $method) use($input) {
                return self::startsWith($input, $method->getName());
            })
            ->map(function (ReflectionMethod $method) {
                return $method->getName().'('.($method->getNumberOfRequiredParameters() > 0 ? '' : ')');
            })
            ->distinct()
            ->toArray(false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatched(array $tokens)
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

        return (new QueryResolver())->resolve($buffer->before()) !== null;
    }
}
