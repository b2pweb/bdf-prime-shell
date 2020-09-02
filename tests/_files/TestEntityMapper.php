<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Mapper\Builder\FieldBuilder;
use Bdf\Prime\Mapper\Mapper;

class TestEntityMapper extends Mapper
{
    public function schema()
    {
        return [
            'connection' => 'test',
            'table' => 'test_entity'
        ];
    }

    public function buildFields($builder)
    {
        $builder
            ->integer('id')->autoincrement()
            ->string('value')
            ->embedded('relation', RelationEntity::class, function (FieldBuilder $builder) {
                $builder->integer('id')->alias('relation_id')->nillable();
            })
        ;
    }

    public function buildRelations($builder)
    {
        $builder->on('relation')->belongsTo(RelationEntity::class, 'relation.id');
    }
}
