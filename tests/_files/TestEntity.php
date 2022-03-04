<?php

namespace Bdf\Prime\Shell\_files;

use Bdf\Prime\Entity\InitializableInterface;
use Bdf\Prime\Entity\Model;

/**
 *
 */
class TestEntity extends Model implements InitializableInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @var RelationEntity
     */
    private $relation;

    /**
     * @var TestEntity
     */
    private $r2;

    public function __construct(array $data = [])
    {
        $this->initialize();
        $this->import($data);
    }

    public function initialize(): void
    {
        $this->relation = new RelationEntity();
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
     * @return TestEntity
     */
    public function setId(int $id): TestEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return TestEntity
     */
    public function setValue(string $value): TestEntity
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return RelationEntity
     */
    public function getRelation(): RelationEntity
    {
        return $this->relation;
    }

    /**
     * @param RelationEntity $relation
     * @return TestEntity
     */
    public function setRelation(RelationEntity $relation): TestEntity
    {
        $this->relation = $relation;
        return $this;
    }

    /**
     * @return TestEntity
     */
    public function r2(): TestEntity
    {
        return $this->r2;
    }

    /**
     * @param TestEntity $r2
     * @return TestEntity
     */
    public function setR2(TestEntity $r2): TestEntity
    {
        $this->r2 = $r2;
        return $this;
    }
}
