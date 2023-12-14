<?php

namespace Alms\Bundle\CycleBundle\Repository\Trait;

use Cycle\ORM\EntityManagerInterface;

trait PersistRepositoryTrait
{
    protected EntityManagerInterface|null $entityManager;

    public function save(object $entity, bool $run = true): void
    {
        $this->entityManager->persist($entity);

        if ($run) {
            $this->entityManager->run();
        }
    }

    public function remove(object $entity, bool $run = true): void
    {
        $this->entityManager->delete($entity);

        if ($run) {
            $this->entityManager->run();
        }
    }

    public function run(): void
    {
        $this->entityManager->run();
    }
}