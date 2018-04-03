# ACL

`EasyAdminPlus` is packaged with ACL to restrict access based on `entity/action role permissions`.

> *Note: it is a simple porting of the great implementation by Pierre-Charles Bertineau on [EasyAdminExtensionBundle](https://github.com/alterphp/EasyAdminExtensionBundle), thanks to him.*

You can define roles to restrict access at `Entity` level or at `Action` level.

### Per entity role permissions

Defining a global role for the whole `Entity` access.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            role: ROLE_ADMIN_SUPER
```

All the enabled actions on the Product `Entity` will be only accessible to users with role `ROLE_ADMIN_SUPER`.

### Per entity action role permissions

Defining a specific role for all the actions of the `Entity`.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            list:
                role: ROLE_ADMIN_READER
            search:
                role: ROLE_ADMIN_READER
            new:
                role: ROLE_ADMIN_SUPER
            edit:
                role: ROLE_ADMIN_SUPER
            show:
                role: ROLE_ADMIN_READER
            delete:
                role: ROLE_ADMIN_SUPER
```

Let's admit you use this kind of role hierarchy:

```yaml
# config/packages/security.yaml
security:
	###
	role_hierarchy:
        ROLE_USER:        	ROLE_USER
        ROLE_ADMIN:       	ROLE_USER # admin (minimum level to access back-office)
		ROLE_ADMIN_READER: 	ROLE_ADMIN # custom admin role
        ROLE_ADMIN_SUPER: 	[ROLE_USER, ROLE_ADMIN, ROLE_ADMIN_READER] # super-admin
```

User with role `ROLE_ADMIN_READER` can only access to `List`, `Search` and `Show` actions of the Product `Entity` whereas `ROLE_ADMIN_SUPER` can access all the actions with no restriction.

> *Note: If you use actions based roles, you can do some factoring by naming all your roles with the action name as suffix.*

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

### Front-End

The items in the menu are reduced to only display Entities you're allowed to access.

In the same way, action buttons (`New`, `Edit`, `Show`, `Delete`) and the search form are only displayed with you own the correct privileges.

If you are writing a custom action, you can use the twig filter to check a privilege for a given entity.

```twig
{# templates/Admin/test.html.twig #}
{% if is_easyadmin_granted('Product', 'new') %}
    <a href="...">Add a product</a>
  {% endif %}
```

----------

Next chapter: [Chapter 5 - Export Action](chapter-5.md)