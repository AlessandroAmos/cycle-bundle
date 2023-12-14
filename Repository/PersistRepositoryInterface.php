<?php

namespace Alms\Bundle\CycleBundle\Repository;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\RepositoryInterface;

interface PersistRepositoryInterface extends RepositoryInterface
{
    public function setEntityManager(EntityManagerInterface $entityManager): void;

    public function save(object $entity, bool $run = true): void;

    public function remove(object $entity, bool $run = true): void;

    public function run(): void;
}