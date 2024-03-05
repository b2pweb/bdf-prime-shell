<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Mapper\Builder\FieldBuilder;
use Bdf\Prime\Mapper\Mapper;
use Bdf\Prime\Repository\EntityRepository;

class TestEntityMapper extends Mapper
{
    public function schema(): array
    {
        return [
            'connection' => 'test',
            'table' => 'test_entity'
        ];
    }

    public function buildFields($builder): void
    {
        $builder
            ->integer('id')->autoincrement()
            ->string('value')
            ->embedded('relation', RelationEntity::class, function (FieldBuilder $builder) {
                $builder->integer('id')->alias('relation_id')->nillable();
            })
        ;
    }

    public function buildRelations($builder): void
    {
        $builder->on('relation')->belongsTo(RelationEntity::class, 'relation.id');
        $builder->on('r2')->belongsTo(TestEntity::class, 'value');

        if (method_exists($builder, 'null')) {
            $builder->on('noop')->null()->detached();
        }
    }

    public function scopes(): array
    {
        return [
            'myScope' => function ($query, $value) {
                return $query->where('value', ':like', '%'.$value.'%');
            }
        ];
    }

    public function queries(): array
    {
        return [
            'myQuery' => function ($repository, $value) {
                return $repository->keyValue('value', $value);
            },

            'dangerousQuery' => function (EntityRepository $repository) {
                $repository->schema()->drop();
            },
        ];
    }
}
