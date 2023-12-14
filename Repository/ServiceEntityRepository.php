<?php

namespace Alms\Bundle\CycleBundle\Repository;

use Cycle\ORM\EntityManager;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;

abstract class ServiceEntityRepository extends Repository implements RepositoryInterface
{
    public function __construct(ORMInterface $orm, string $entity)
    {
        parent::__construct(new Select($orm, $entity));

        if ($this instanceof PersistRepositoryInterface) {
            $this->setEntityManager(new EntityManager($orm));
        }
    }
}