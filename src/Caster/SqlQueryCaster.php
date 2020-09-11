<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Query\SqlQueryInterface;

/**
 * Caster for SQL queries
 *
 * @extends AbstractQueryCaster<SqlQueryInterface>
 */
final class SqlQueryCaster extends AbstractQueryCaster
{
    /**
     * {@inheritdoc}
     */
    public function type(): string
    {
        return SqlQueryInterface::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param SqlQueryInterface $query
     */
    protected function dumpQuery(CommandInterface $query): array
    {
        return ['SQL' => $query->toRawSql()];
    }
}
