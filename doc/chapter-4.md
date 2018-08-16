# ACL

`EasyAdminPlus` is packaged with ACL to restrict access based on `entity/action role permissions`.

> *Note: it is a simple porting of the great implementation by Pierre-Charles Bertineau on [EasyAdminExtensionBundle](https://github.com/alterphp/EasyAdminExtensionBundle), thanks to him.*

You can define roles to restrict access at `Entity` level or at `Action` level.

-------

### Per entity role permissions

Defining a global role for the whole `Entity` access.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            role: ROLE_EASY_ADMIN_SUPER
```

All the enabled actions on the Product `Entity` will be only accessible to users with role `ROLE_EASY_ADMIN_SUPER`.

-------

### Per entity action role permissions

Defining a specific role for all the actions of the `Entity`.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            list:
                role: ROLE_EASY_ADMIN_READER
            search:
                role: ROLE_EASY_ADMIN_READER
            new:
                role: ROLE_EASY_ADMIN_SUPER
            edit:
                role: ROLE_EASY_ADMIN_SUPER
            show:
                role: ROLE_EASY_ADMIN_READER
            delete:
                role: ROLE_EASY_ADMIN_SUPER
```

Let's admit you use this kind of role hierarchy:

```yaml
# config/packages/security.yaml
security:
    ### ...
    role_hierarchy:
        ROLE_USER:              ROLE_USER
        ROLE_EASY_ADMIN:        ROLE_USER # admin (minimum level to access back-office)
        ROLE_EASY_ADMIN_READER: ROLE_EASY_ADMIN # custom admin role
        ROLE_EASY_ADMIN_SUPER: 	[ROLE_USER, ROLE_EASY_ADMIN, ROLE_EASY_ADMIN_READER] # super-admin
```

User with role `ROLE_EASY_ADMIN_READER` can only access to `List`, `Search` and `Show` actions of the Product `Entity` whereas `ROLE_EASY_ADMIN_SUPER` can access all the actions with no restriction.

-------

### Using actions based roles 

You can do some factoring by naming all your roles with the action name as suffix.

Both following configurations will apply exactly the same restrictions.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            list:
                role: ROLE_ADMIN_PRODUCT_LIST
            search:
                role: ROLE_ADMIN_PRODUCT_SEARCH
            edit:
                role: ROLE_ADMIN_PRODUCT_EDIT
            show:
                role: ROLE_ADMIN_PRODUCT_SHOW
            delete:
                role: ROLE_ADMIN_PRODUCT_DELETE
```

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            role_prefix: ROLE_ADMIN_PRODUCT
```

Entity `role_prefix` defines all actions required roles by appending the action name to the prefix

-------

### Front-End

The items in the menu are reduced to only display Entities you're allowed to access.

In the same way, action buttons (`New`, `Edit`, `Show`, `Delete`) and the search form are only displayed if you own the correct privileges.

If you are writing a custom action, you can use the twig filter to check a privilege for a given entity.

```twig
{# templates/Admin/test.html.twig #}
{% if is_easyadmin_granted('Product', 'new') %}
    <a href="...">Add a product</a>
{% endif %}
```

-------

### Advanced ACL and voters

You can also handle more precise right on the object. It's really needed for action right. Some time you can delete a custommer only if he doesn't have any Invoice. In Symfony you can do that with Voters ( http://symfony.com/doc/current/security/voters.html )
In EasyAdmin you cannot do that directly

In EasyAdminPlus, if you want to use your voter you have to do the following things:

```php
<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\Customer;

const ROLE_PREFIX='ROLE_CUSTOMER_';

class CustomerVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        if (in_array($attribute, [ROLE_PREFIX.'LIST', ROLE_PREFIX.'SEARCH'])) {
            return true;
        }
        if ($subject instanceof Customer) {
            return in_array($attribute, [ROLE_PREFIX.'SHOW', ROLE_PREFIX.'EDIT', ROLE_PREFIX.'DELETE']);
        } else {
            return false;
        }
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case ROLE_PREFIX.'LIST':
                return true;
            case ROLE_PREFIX.'SEARCH':
                return true;
            case ROLE_PREFIX.'SHOW':
                return $this->canView($subject, $token, $user);
                break;
            case ROLE_PREFIX.'EDIT':
                return $this->canEdit($subject, $token, $user);
            case ROLE_PREFIX.'DELETE':
                return $this->canDelete($subject, $token, $user);
                break;
        }

        return false;
    }

    private function canView(Customer $customer, $token,$user)
    {
        return true;
    }

    private function canEdit(Customer $customer, $token,$user)
    {
        return false;
    }

    private function canDelete(Customer $customer,$token, $user)
    {
        if( count($customer->getInvoices()) > 0 ) {
            return false;
        }
        return true;
    }   
}

```

You have also to register your voter with a priority greater than the role_hierarchy_voter. If you don't do that you need to declare all the sub role in you security roles. ( not very fun )

Instead of that you can juste declare your voter in services.yml like.

```
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Security}'

    App\Security\Voter\:
        resource: '../src/Security/Voter'
        autoconfigure: false
        tags:
          - { name: 'security.voter', priority: 280 }
```
Don't forget to exclude the Security Foler in auto service declaration ( first 3 lines )

Déclare your entity with a prefix role.

```
easy_admin:
    entities:
        Customer:
            class: App\Entity\Customer
            disabled_actions: []
            role_prefix: ROLE_CUSTOMER
```

-------

### Known issues

For the default access to admin area, Javier takes the first `Entity` in the settings and forward on it with default action `List`.

The problem is that he's not checking if the `List` action of this entity is really enabled.

I've submitted a [PR](https://github.com/EasyCorp/EasyAdminBundle/pull/2151) to force the redirect on the correct `Entity`, `Action` and `Id` (if `Edit` action) but still not merged.

With the addition of the role feature, if the first defined `Entity` requires a higher role than yours, we've no other choice than choosing the first `Entity` which matchs with your role **on the `List` Action**.
 
So sadly, if your first defined `Entity` matchs with your role but on a different action than `List`, it'll be skipped.

----------

Next chapter: [Chapter 5 - Export Action](chapter-5.md)