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
use Psy\Context;
use Psy\ContextAware;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionException;
use ReflectionObject;

/**
 * Autocomplete the columns on query methods parameters, like where
 */
final class QueryColumnMatcher extends AbstractMatcher implements ContextAware
{
    use QueryExtensionGetterTrait;
    use StreamTrait;

    /**
     * @var QueryResolver
     */
    private $resolver;

    /**
     * QueryColumnMatcher constructor.
     */
    public function __construct()
    {
        $this->resolver = new QueryResolver();
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
    public function getMatches(array $tokens, array $info = [])
    {
        $parsed = $this->parseTokens($tokens);

        $query = $parsed['query'];
        $input = ltrim($parsed['input'], '\'"');
        $quote = $parsed['quote'];

        $repository = $this->getExtensionRepository($query);

        if (!$repository) {
            return [];
        }

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

    /**
     * @param array<int, string|array> $tokens
     * @return array{input: string, query: CommandInterface, method: string, quote: string}|null
     * @psalm-ignore-nullable-return
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
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

        $parsed['method'] = $buffer->asString(-1);

        // Handle query()->method("
        if ($buffer->is(T_OBJECT_OPERATOR)) {
            if (($query = $this->resolver->resolve($buffer->before())) === null) {
                return null;
            }

            $parsed['query'] = $query;

            return $parsed;
        }

        // Handle Entity::method("
        if (!$buffer->is(T_DOUBLE_COLON) || !Model::isActiveRecordEnabled()) {
            return null;
        }

        $className = $buffer->next()->fullyQualifiedClassName();

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
     * @param array $visitedRepositories
     *
     * @return string[]
     */
    private function getAllAttributes(RepositoryInterface $repository, string $prefix = '', array &$visitedRepositories = []): array
    {
        if (in_array($repository, $visitedRepositories, true)) {
            return [];
        }

        $attributes = [];
        $visitedRepositories[] = $repository;

        foreach ($repository->metadata()->attributes as $attribute => $_) {
            $attributes[] = $prefix.$attribute;
        }

        foreach ($repository->mapper()->relations() as $relation => $_) {
            $distant = $repository->relation($relation)->relationRepository();

            /** @psalm-suppress DocblockTypeContradiction */
            if (!$distant) {
                continue;
            }

            foreach ($this->getAllAttributes($distant, $prefix.$relation.'.', $visitedRepositories) as $attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
