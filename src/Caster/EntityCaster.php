<?php

namespace Bdf\Prime\Shell\Caster;

use Bdf\Prime\Entity\EntityInterface;
use Bdf\Prime\ServiceLocator;

/**
 * Caster for entity objects
 *
 * @implements PrimeCasterInterface<EntityInterface>
 */
final class EntityCaster implements PrimeCasterInterface
{
    /**
     * @var ServiceLocator
     */
    private $prime;

    /**
     * EntityCaster constructor.
     * @param ServiceLocator $prime
     */
    public function __construct(ServiceLocator $prime)
    {
        $this->prime = $prime;
    }

    /**
     * {@inheritdoc}
     */
    public function type(): string
    {
        return EntityInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($object): array
    {
        return $this->filter($this->prime->hydrator($object)->extract($object));
    }

    private function filter(array $entity): array
    {
        foreach ($entity as $key => $value) {
            if ($value === null) {
                unset($entity[$key]);
            } elseif (is_array($value)) {
                $value = $this->filter($value);

                if (empty($value)) {
                    unset($entity[$key]);
                } else {
                    $entity[$key] = $value;
                }
            }
        }

        return $entity;
    }
}
