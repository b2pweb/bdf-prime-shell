<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Prime\Entity\Model;
use Bdf\Prime\Query\CommandInterface;
use ReflectionObject;
use Throwable;

/**
 * Add query method autocomplete
 * Handle method chaining
 */
final class QueryResolver
{
    /**
     * @var CommandInterface|null
     */
    private $lastQuery;

    /**
     * @var array
     */
    private $lastTokens;

    /**
     * @param TokensBuffer $buffer
     * @return CommandInterface|null
     *
     * @todo handle variable
     * @todo cache
     */
    final public function resolve(TokensBuffer $buffer): ?CommandInterface
    {
        if ($this->lastQuery !== null && $this->lastTokens == $buffer->all()) {
            return $this->lastQuery;
        }

        $this->lastQuery = null;
        $this->lastTokens = null;

        $buffer->forward();
        $class = $buffer->next()->fullyQualifiedClassName();

        if (!is_subclass_of($class, Model::class)) {
            return null;
        }

        if (!$buffer->match(T_DOUBLE_COLON, T_STRING)) {
            return null;
        }

        // Ignore repository method call
        if ($buffer->equals('repository', -1)) {
            $buffer->next(4); // skip repository and parenthesis and object operator
        }

        if (!$buffer->valid()) {
            return null;
        }

        $repository = $class::repository();
        $method = $buffer->asString(-1);

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

        $buffer->goTo(1);

        while ($buffer->valid()) {
            $strToken = $buffer->asString();

            // Disallow execution calls
            if (in_array(strtolower($strToken), ['execute', 'first', 'all', 'inrows', 'inrow', 'update', 'insert', 'replace', 'find', 'findone', 'count', 'sum', 'avg', 'max', 'min', 'aggregate'])) {
                return null;
            }

            $line .= $strToken;
            $buffer->next();
        }

        try {
            $query = eval("return $line;");
        } catch (Throwable $e) {
            return null;
        }

        if (!$query instanceof CommandInterface) {
            return null;
        }

        $this->lastQuery = $query;
        $this->lastTokens = $buffer->all();

        return $query;
    }
}
