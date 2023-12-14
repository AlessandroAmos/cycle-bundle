# Creating a repository

with `cycle orm bundle` you can create a repository for your entities using two ways:

- Using `Alms\Bundle\CycleBundle\Repository\RepositoryTrait` trait

```php

use Alms\Bundle\CycleBundle\Repository\Trait\RepositoryTrait;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select\RepositoryInterface;

class UserRepository implements RepositoryInterface
{
    use RepositoryTrait
    
    public function __construct(ORMInterface $orm)
    {
        $this->configure($orm, User::class);
    }
}
```
- Extending `Alms\Bundle\CycleBundle\Repository\ServiceEntityRepository` class

```php

use Alms\Bundle\CycleBundle\Repository\ServiceEntityRepository;
use Cycle\ORM\ORMInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ORMInterface $ORM)
    {
        parent::__construct($ORM, User::class);
    }
}
```

Both ways get the same result, but the first one doesn't require to extend a class, so your code will be more flexible.

## Persist Repository

For using repository with persisting entities you need to add `Alms\Bundle\CycleBundle\Repository\PersistRepositoryTrait` trait to your repository class and implement `Alms\Bundle\CycleBundle\Repository\PersistRepositoryInterface` interface.

```php
use Alms\Bundle\CycleBundle\Repository\Trait\RepositoryTrait;
use Alms\Bundle\CycleBundle\Repository\Trait\PersistRepositoryTrait;
use Alms\Bundle\CycleBundle\Repository\PersistRepositoryInterface;
use Cycle\ORM\ORMInterface;

class UserRepository implements PersistRepositoryInterface
{
    use RepositoryTrait, PersistRepositoryTrait;
    
    public function __construct(ORMInterface $orm)
    {
        $this->configure($orm, User::class);
    }
    
}
```

or using `Alms\Bundle\CycleBundle\Repository\ServiceEntityRepository` class

```php
use Alms\Bundle\CycleBundle\Repository\ServiceEntityRepository;
use Alms\Bundle\CycleBundle\Repository\PersistRepositoryInterface;

class UserRepository extends ServiceEntityRepository implements PersistRepositoryInterface
{
    use PersistRepositoryTrait;
    
    public function __construct(ORMInterface $ORM)
    {
        parent::__construct($ORM, User::class);
    }
}
```

Then you can use `PersistRepositoryTrait` methods for persisting entities.

```php
$user = new User();
$repository = new UserRepository($orm);

$repository->save($user);
```

> **Note:** `PersistRepositoryTrait` needs implemented `Alms\Bundle\CycleBundle\Repository\PersistRepositoryInterface` interface for working properly.