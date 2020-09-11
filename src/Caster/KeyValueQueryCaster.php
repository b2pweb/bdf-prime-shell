<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Query\Custom\KeyValue\KeyValueQuery;

/**
 * Caster for SQL queries
 *
 * @extends AbstractQueryCaster<KeyValueQuery>
 */
final class KeyValueQueryCaster extends AbstractQueryCaster
{
    /**
     * {@inheritdoc}
     */
    public function type(): string
    {
        return KeyValueQuery::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param KeyValueQuery $query
     */
    protected function dumpQuery(CommandInterface $query): array
    {
        $dump = ['SQL' => $query->toSql()];

        if ($query->getBindings()) {
            $dump['bindings'] = $query->getBindings();
        }

        return $dump;
    }
}
