<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Query\QueryRepositoryExtension;
use Bdf\Prime\Query\SqlQueryInterface;
use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;

/**
 * Caster for SQL queries
 */
final class SqlQueryCaster implements PrimeCasterInterface
{
    use QueryExtensionGetterTrait;

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
     * @param SqlQueryInterface $object
     */
    public function __invoke($object): array
    {
        $out = ['SQL' => $object->toRawSql()];
        $out += $this->dumpExtension($object);

        return $out;
    }

    private function dumpExtension(SqlQueryInterface $query): array
    {
        $extension = $this->getExtension($query);

        if (!$extension instanceof QueryRepositoryExtension) {
            return [];
        }

        $extension = (array) $extension;

        return array_filter([
            'entity' => $extension["\0*\0metadata"]->entityName,
            'with' => $this->clearRelations($extension["\0*\0withRelations"]),
            'without' => $this->clearRelations($extension["\0*\0withoutRelations"]),
            'by' => $extension["\0*\0byOptions"],
        ]);
    }

    private function clearRelations(array $relations)
    {
        foreach ($relations as $name => $value) {
            $relations[$name] = array_filter($value);
        }

        return $relations;
    }
}
