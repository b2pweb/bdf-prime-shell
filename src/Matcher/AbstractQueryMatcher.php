<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Prime\Entity\Model;
use Bdf\Prime\Query\CommandInterface;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionObject;

/**
 * Add query method autocomplete
 * Handle method chaining
 *
 * @todo Utility class
 * @todo refactor
 */
abstract class AbstractQueryMatcher extends AbstractMatcher
{
    /**
     * @var CommandInterface|null
     */
    private $lastQuery;

    /**
     * @var array|null
     */
    private $lastTokens;


    /**
     * @param array $tokens
     * @return CommandInterface|null
     *
     * @todo handle variable
     */
    final protected function getQuery(array $tokens): ?CommandInterface
    {
        if ($this->lastQuery !== null && $tokens == $this->lastTokens) {
            return $this->lastQuery;
        }

        $this->lastQuery = null;
        $this->lastTokens = null;

        $class = '';
        $current = 1;

        for (; isset($tokens[$current]) && self::hasToken([self::T_NS_SEPARATOR, self::T_STRING], $tokens[$current]); ++$current) {
            $class .= $tokens[$current][1];
        }

        if (!is_subclass_of($class, Model::class)) {
            return null;
        }

        if (!isset($tokens[$current]) || !self::tokenIs($tokens[$current++], self::T_DOUBLE_COLON)) {
            return null;
        }

        if (!isset($tokens[$current]) || !self::tokenIs($tokens[$current], self::T_STRING)) {
            return null;
        }

        // Ignore repository method call
        if ($tokens[$current][1] === 'repository') {
            $current += 4; // skip repository and parenthesis and object operator
        }

        if (!isset($tokens[$current])) {
            return null;
        }

        $repository = $class::repository();
        $method = $tokens[$current][1];

        // @todo check not in repository nor query factory
        $availableMethods = ['builder', 'with', 'without', 'by', 'wrapAs', 'where', 'make', 'keyValue'];
        $availableMethods = array_merge(
            $availableMethods,
            array_map(
                new Getter('getName'),
                (new ReflectionObject($repository->builder()))->getMethods()
            )
        );

        if (!in_array($method, $availableMethods)) {
            return null;
        }

        $line = '';

        foreach (array_slice($tokens, 1) as $token) {
            $strToken = is_array($token) ? $token[1] : $token;

            // Disallow execution calls
            if (in_array(strtolower($strToken), ['execute', 'first', 'all', 'inRows', 'inRow', 'update', 'insert', 'replace', 'find', 'findone', 'count', 'sum', 'avg', 'max', 'min', 'aggregate'])) {
                return null;
            }

            $line .= $strToken;
        }

        $query = eval('return '.$line.';');

        if (!$query instanceof CommandInterface) {
            return null;
        }

        $this->lastQuery = $query;
        $this->lastTokens = $tokens;

        return $query;
    }
}
