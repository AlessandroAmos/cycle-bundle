<?php

namespace Alms\Bundle\CycleBundle\Security;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CycleUserProvider implements UserProviderInterface
{

    public function __construct(
        protected readonly ORMInterface $orm,
        protected readonly string       $class,
        protected readonly string       $property,
    )
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $repository = $this->getRepository();

        $user = $repository->findOne([$this->property => $identifier]);

        if (null === $user) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);

            throw $e;
        }

        return $user;
    }

    /**
     * @throws \ReflectionException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = $this->class;

        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $repository = $this->getRepository();

        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {

            $primaryKeys = $this->orm->getSchema()->define($this->class, SchemaInterface::PRIMARY_KEY);

            $ref = new \ReflectionObject($user);

            $condition = [];

            foreach ($primaryKeys as $primaryKey)
            {
                $property = $ref->getProperty($primaryKey);
                $condition[$primaryKey] = $property->getValue($user);
            }

            $refreshedUser = $repository->findOne($condition);
        }

        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    private function getRepository(): RepositoryInterface
    {
        return $this->orm->getRepository($this->class);
    }

}