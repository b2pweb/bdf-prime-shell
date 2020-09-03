<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Prime\Entity\Model;
use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Repository\RepositoryInterface;
use Error;
use InvalidArgumentException;
use Psy\Context;
use Psy\ContextAware;
use ReflectionObject;
use Throwable;

/**
 * Add query method autocomplete
 * Handle method chaining
 */
final class QueryResolver implements ContextAware
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
     * @var Context
     */
    private $context;


    /**
     * {@inheritdoc}
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param TokensBuffer $buffer
     * @return CommandInterface|null
     */
    final public function resolve(TokensBuffer $buffer): ?CommandInterface
    {
        // Get last resolved query
        if ($this->lastQuery !== null && $this->lastTokens == $buffer->all()) {
            return $this->lastQuery;
        }

        // Clear cache
        $this->lastQuery = null;
        $this->lastTokens = null;

        $query = $this->doResolve($buffer);

        // Store resolved query in cache
        $this->lastQuery = $query;
        $this->lastTokens = $buffer->all();

        return $query;
    }

    /**
     * Resolve the query from the buffer
     *
     * @param TokensBuffer $buffer
     * @return CommandInterface|null
     */
    private function doResolve(TokensBuffer $buffer): ?CommandInterface
    {
        $buffer->forward()->next(); // Ignore PHP open tag

        if ($this->context !== null && $buffer->is(T_VARIABLE)) {
            try {
                $query = $this->resolveFromVariable(
                    $this->context->get(ltrim($buffer->asString(), '$')),
                    $buffer
                );
            } catch (InvalidArgumentException $e) {
                return null;
            }
        } else {
            $query = $this->resolveFromStaticCall(
                $buffer->fullyQualifiedClassName(),
                $buffer
            );
        }

        return $query instanceof CommandInterface ? $query : null;
    }

    /**
     * Resolve the query from a variable
     *
     * @param mixed $value The variable value
     * @param TokensBuffer $buffer
     *
     * @return mixed
     */
    private function resolveFromVariable($value, TokensBuffer $buffer)
    {
        if ($value instanceof CommandInterface) {
            return $this->eval($buffer);
        }

        if ($value instanceof RepositoryInterface) {
            if (!$buffer->next()->match(T_OBJECT_OPERATOR, T_STRING)) {
                return null;
            }

            if (!$this->checkRepositoryMethodCall($value, $buffer->asString(-1))) {
                return null;
            }

            return $this->eval($buffer);
        }

        return null;
    }

    /**
     * Resolve a query from a query created by a static call on model class
     *
     * @param string $entityClassName
     * @param TokensBuffer $buffer
     *
     * @return mixed|null
     */
    private function resolveFromStaticCall(string $entityClassName, TokensBuffer $buffer)
    {
        if (!is_subclass_of($entityClassName, Model::class)) {
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

        if (!$this->checkModelMethodCall($entityClassName, $buffer->asString(-1))) {
            return null;
        }

        return $this->eval($buffer);
    }

    /**
     * Evaluate the tokens
     *
     * @param TokensBuffer $buffer
     *
     * @return mixed|null
     */
    private function eval(TokensBuffer $buffer)
    {
        $line = '';

        $buffer->goTo(1); // Ignore PHP open tag

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
            if ($this->context !== null) {
                foreach ($this->context->getAll() as $varName => $value) {
                    try {
                        $$varName = is_object($value) ? clone $value : $value;
                    } catch (Error $e) {
                        // Ignore clone error
                    }
                }
            }

            return eval("return $line;");
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Check if the static method call is a valid query factory method (i.e. MyEntity::builder())
     *
     * @param string $modelClass The model class name
     * @param string $methodName The method name
     *
     * @return bool
     */
    private function checkModelMethodCall(string $modelClass, string $methodName): bool
    {
        return $this->checkRepositoryMethodCall($modelClass::repository(), $methodName);
    }

    /**
     * Check if the repository method is valid for create a query
     *
     * @param RepositoryInterface $repository
     * @param string $methodName The method name
     *
     * @return bool
     */
    private function checkRepositoryMethodCall(RepositoryInterface $repository, string $methodName): bool
    {
        // @todo check not in repository nor query factory
        $availableMethods = ['builder', 'with', 'without', 'by', 'wrapAs', 'where', 'make', 'keyValue'];
        $availableMethods = array_merge(
            $availableMethods,
            array_map(
                new Getter('getName'),
                (new ReflectionObject($repository->builder()))->getMethods()
            )
        );

        return in_array($methodName, $availableMethods);
    }
}
