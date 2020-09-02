<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Mapper\Mapper;

class RelationEntityMapper extends Mapper
{
    public function schema()
    {
        return [
            'connection' => 'test',
            'table' => 'relation_entity'
        ];
    }

    public function buildFields($builder)
    {
        $builder
            ->integer('id')->autoincrement()
            ->string('name')
        ;
    }
}
