<?php

namespace Bdf\Prime\Shell\Util;

use Bdf\Prime\Query\AbstractReadCommand;
use Bdf\Prime\Query\CommandInterface;
use Bdf\Prime\Query\QueryRepositoryExtension;
use Bdf\Prime\Repository\RepositoryInterface;
use ReflectionProperty;

/**
 * Utility trait for extract the query extension from the query object
 */
trait QueryExtensionGetterTrait
{
    /**
     * @var ReflectionProperty|null
     */
    private $extensionProperty;


    /**
     * Get the query extension from the query instance
     *
     * @param CommandInterface|null $query
     * @return object|null
     */
    private function getExtension(?CommandInterface $query)
    {
        if (!$query instanceof AbstractReadCommand) {
            return null;
        }

        if ($this->extensionProperty === null) {
            $this->extensionProperty = new ReflectionProperty(AbstractReadCommand::class, 'extension');
            $this->extensionProperty->setAccessible(true);
        }

        return $this->extensionProperty->getValue($query);
    }

    /**
     * @param CommandInterface|null $query
     *
     * @return RepositoryInterface|null
     */
    private function getExtensionRepository(?CommandInterface $query): ?RepositoryInterface
    {
        if (!($extension = $this->getExtension($query)) instanceof QueryRepositoryExtension) {
            return null;
        }

        return ((array) $extension)["\0*\0repository"];
    }
}
