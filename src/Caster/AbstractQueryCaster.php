<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Analyzer\AnalyzerService;
use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Query\CompilableClause;
use Bdf\Prime\Query\QueryRepositoryExtension;
use Bdf\Prime\Shell\Util\QueryExtensionGetterTrait;

/**
 * Base caster for queries
 *
 * @template T of CommandInterface
 * @implements PrimeCasterInterface<T>
 */
abstract class AbstractQueryCaster implements PrimeCasterInterface
{
    use QueryExtensionGetterTrait;

    /**
     * @var AnalyzerService
     */
    private $analyzerService;

    /**
     * SqlQueryCaster constructor.
     *
     * @param AnalyzerService $analyzerService
     */
    public function __construct(AnalyzerService $analyzerService)
    {
        $this->analyzerService = $analyzerService;
    }

    /**
     * {@inheritdoc}
     *
     * @param T $object
     */
    final public function __invoke($object): array
    {
        $out = $this->dumpQuery($object);
        $out += $this->dumpExtension($object);
        $out += $this->dumpAnalysis($object);

        return $out;
    }

    /**
     * Dump query information like SQL or bindings
     *
     * @param T $query
     *
     * @return array
     */
    abstract protected function dumpQuery(CommandInterface $query): array;

    private function dumpExtension(CommandInterface $query): array
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

    private function clearRelations(array $relations): array
    {
        foreach ($relations as $name => $value) {
            $relations[$name] = array_filter($value);
        }

        return $relations;
    }

    private function dumpAnalysis(CommandInterface $query): array
    {
        /** @psalm-suppress RedundantCondition */
        if (!$query instanceof CompilableClause) {
            return [];
        }

        $report = $this->analyzerService->analyze($query);

        if (!$report || !$report->errors()) {
            return [];
        }

        return ['analysis' => $report->errors()];
    }
}
