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

### Known issues

For the default access to admin area, Javier takes the first `Entity` in the settings and forward on it with default action `List`.

The problem is that he's not checking if the `List` action of this entity is really enabled.

I've submitted a [PR](https://github.com/EasyCorp/EasyAdminBundle/pull/2151) to force the redirect on the correct `Entity`, `Action` and `Id` (if `Edit` action) but still not merged.

With the addition of the role feature, if the first defined `Entity` requires a higher role than yours, we've no other choice than choosing the first `Entity` which matchs with your role **on the `List` Action**.
 
So sadly, if your first defined `Entity` matchs with your role but on a different action than `List`, it'll be skipped.

----------

Next chapter: [Chapter 5 - Export Action](chapter-5.md)