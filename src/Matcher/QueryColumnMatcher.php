<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Collection\Stream\Streams;
use Bdf\Prime\Entity\Model;
use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Repository\RepositoryInterface;
use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;
use Bdf\Prime\Shell\Util\QueryResolver;
use Bdf\Prime\Shell\Util\StreamTrait;
use Bdf\Prime\Shell\Util\TokensBuffer;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionException;
use ReflectionObject;

/**
 * Autocomplete the columns on query methods parameters, like where
 */
final class QueryColumnMatcher extends AbstractMatcher
{
    use QueryExtensionGetterTrait;
    use StreamTrait;

    /**
     * {@inheritdoc}
     */
    public function getMatches(array $tokens, array $info = [])
    {
        $parsed = $this->parseTokens($tokens);

        $query = $parsed['query'];
        $input = ltrim($parsed['input'], '\'"');
        $quote = $parsed['quote'];

        $repository = $this->getExtensionRepository($query);

        return Streams::wrap($this->getAllAttributes($repository))
            ->distinct()
            ->filter(function (string $attribute) use ($input) { return self::startsWith($input, $attribute); })
            ->map(function (string $attribute) use ($quote) { return addslashes($attribute).$quote; })
            ->toArray(false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatched(array $tokens)
    {
        if (($parsed = $this->parseTokens($tokens)) === null) {
            return false;
        }

        return $this->checkMethod($parsed['query'], $parsed['method']);
    }

    private function parseTokens(array $tokens): ?array
    {
        $hasOpenQuote = false;
        $parsed = [
            'input' => '',
            'query' => null,
            'method' => null,
            'quote' => "'",
        ];

        $buffer = new TokensBuffer($tokens);
        $buffer->reverse();

        if ($buffer->is(T_ENCAPSED_AND_WHITESPACE)) {
            $hasOpenQuote = true;
            $parsed['input'] = $buffer->asString();
            $buffer->next();
        }

        if ($buffer->equals('"')) {
            $hasOpenQuote = true;
            $parsed['quote'] = '"';
            $buffer->next();
        }

        if (!$hasOpenQuote) {
            return null;
        }

        if (!$buffer->match('(', T_STRING)) {
            return null;
        }

        // Handle query()->method("
        if ($buffer->is(T_OBJECT_OPERATOR)) {
            if (($query = (new QueryResolver())->resolve($buffer->before())) === null) {
                return null;
            }

            $parsed['query'] = $query;
            $parsed['method'] = $buffer->next(-1)->asString();

            return $parsed;
        }

        // Handle Entity::method("
        if (!$buffer->is(T_DOUBLE_COLON) || Model::locator() === null) {
            return null;
        }

        $parsed['method'] = $buffer->next(-1)->asString();
        $className = $buffer->next(2)->fullyQualifiedClassName();

        if (!is_subclass_of($className, Model::class)) {
            return null;
        }

        $parsed['query'] = $className::repository()->builder();

        return $parsed;
    }

    /**
     * Check if the method is available for autocomplete column
     *
     * @param CommandInterface $query The query object
     * @param string $methodName The called method
     *
     * @return bool True if the method match
     */
    private function checkMethod(CommandInterface $query, string $methodName): bool
    {
        $reflection = new ReflectionObject($query);

        try {
            $method = $reflection->getMethod($methodName);
        } catch (ReflectionException $e) {
            return false;
        }

        if ($method->getNumberOfParameters() === 0) {
            return false;
        }

        return in_array($method->getParameters()[0]->getName(), ['column', 'columns']);
    }

    /**
     * Extract all available attributes for the given repository
     *
     * @param RepositoryInterface $repository
     * @param string $prefix
     *
     * @return string[]
     */
    private function getAllAttributes(RepositoryInterface $repository, string $prefix = ''): array
    {
        $attributes = [];

        foreach ($repository->metadata()->attributes as $attribute => $_) {
            $attributes[] = $prefix.$attribute;
        }

        foreach ($repository->mapper()->relations() as $relation => $_) {
            foreach ($this->getAllAttributes($repository->relation($relation)->relationRepository(), $prefix.$relation.'.') as $attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
