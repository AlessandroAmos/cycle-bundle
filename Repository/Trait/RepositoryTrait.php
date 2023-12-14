<?php

namespace Alms\Bundle\CycleBundle\Repository\Trait;

use Alms\Bundle\CycleBundle\Repository\PersistRepositoryInterface;
use Cycle\ORM\EntityManager;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Select;

/**
 * Repository provides ability to load entities and construct queries.
 *
 * @template TEntity of object
 *
 * @implements RepositoryInterface<TEntity>
 */
trait RepositoryTrait
{
    protected Select $select;

    public function __clone()
    {
        $this->select = clone $this->select;
    }

    public function configure(ORMInterface $orm, string $class): void
    {
        $this->select = new Select($orm, $class);

        if ($this instanceof PersistRepositoryInterface) {
            $this->setEntityManager(new EntityManager($orm));
        }
    }

    public function findByPK($id): ?object
    {
        return $this->select()->wherePK($id)->fetchOne();
    }

    public function findOne(array $scope = []): ?object
    {
        return $this->select()->fetchOne($scope);
    }

    public function findAll(array $scope = [], array $orderBy = []): iterable
    {
        return $this->select()->where($scope)->orderBy($orderBy)->fetchAll();
    }

    /**
     * Get selector associated with the repository.
     *
     * @return Select<TEntity>
     */
    public function select(): Select
    {
        return clone $this->select;
    }
}