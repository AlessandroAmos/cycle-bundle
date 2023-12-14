# Using Cycle ORM with Symfony Security

Cycle ORM Bundle provides integration with Symfony Security component. You can use Cycle ORM as a provider for Symfony Security.

## Configuration

To enable Cycle ORM as a provider for Symfony Security you need to configure `security` section in your `config/packages/security.yaml`:

```yaml
security:
    providers:
        users:
            entity:
                class: App\Entity\User
                property: email
```

like Doctrine ORM, Cycle ORM supports `entity` provider. You can use any Cycle ORM entity as a provider for Symfony Security.

### User Entity

Your user entity must implement `Symfony\Component\Security\Core\User\UserInterface` interface. You can use `Cycle\ORM\Promise\ReferenceInterface` as a base class for your user entity:

```php
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier() : string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // do nothing
    }
}
```