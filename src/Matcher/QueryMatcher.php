<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;
use Bdf\Prime\Shell\Util\StreamTrait;
use Bdf\Prime\Shell\Util\TokensBuffer;
use ReflectionMethod;
use function array_pop;

/**
 * Add query method autocomplete
 * Handle method chaining
 */
final class QueryMatcher extends AbstractQueryMatcher
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

        $query = $this->getQuery($tokens);

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
        $buffer->toEnd();

        // query()->xxx
        if ($buffer->is(T_STRING)) {
            $buffer->previous();
        }

        // query()->
        if (!$buffer->is(T_OBJECT_OPERATOR)) {
            return false;
        }

        return $this->getQuery($buffer->before()) !== null;
    }
}
