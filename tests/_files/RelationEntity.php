<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Entity\Model;

/**
 * Class RelationEntity
 * @package Bdf\Prime\Shell\_files
 */
class RelationEntity extends Model
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public function __construct(array $data = [])
    {
        $this->import($data);
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return RelationEntity
     */
    public function setId(int $id): RelationEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RelationEntity
     */
    public function setName(string $name): RelationEntity
    {
        $this->name = $name;
        return $this;
    }
}
