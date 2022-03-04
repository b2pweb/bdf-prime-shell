<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Mapper\Mapper;

class RelationEntityMapper extends Mapper
{
    public function schema(): array
    {
        return [
            'connection' => 'test',
            'table' => 'relation_entity'
        ];
    }

    public function buildFields($builder): void
    {
        $builder
            ->integer('id')->autoincrement()
            ->string('name')
        ;
    }

    public function buildRelations($builder): void
    {
        $builder->on('entity')
            ->hasMany(TestEntity::class.'::relation.id')
            ->detached()
        ;
    }
}
