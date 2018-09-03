# Configurable QueryBuilder

The AdminController create a query builder from the entity.
This extension make possible to use your own query_builder defined in your repository without the need to override the controller.

### Settings

To enable batch actions, you've to add a new node (`batchs`) for the action `list` of the entity.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Ressource:
            class: App\Entity\Ressource
            qb_method: findActiveRessourceQb
            disabled_actions: [new]
            role: ROLE_RESSOURCE
```

The method should return a Qb and MUST use "entity" for the main alias.

```php
    public function findActiveRessourceQb()
    {

        $qb = $this->createQueryBuilder('entity')
            ->join('entity.service','service')
            ->where('service.actif = 1')
        ;
        return $qb;
    }
```

[Back to main readme](../README.md)
