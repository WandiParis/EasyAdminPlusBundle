**DISCLAIMER** BETA VERSION not recommended for production use

# EasyAdminPlusBundle

EasyAdminPlusBundle is a Symfony4 wrapper for the amazing [javiereguiluz/EasyAdminBundle](https://github.com/javiereguiluz/EasyAdminBundle). It includes some extra features.

## How to use

### Install via composer
```
$ composer require wandi/easyadmin-plus-bundle
```

### Configuration
* Update ```config/packages/security.yaml``` configuration: 

```yaml
security:
    encoders:
        # ...
        Wandi\EasyAdminPlusBundle\Entity\User: bcrypt
    
    providers:
        # ...    
        wandi_easy_admin_plus:
            entity: { class: 'Wandi\EasyAdminPlusBundle\Entity\User' }
            
    firewalls:
        wandi_easy_admin_plus:
            pattern: ^/admin
            anonymous: ~
            logout:
                path: wandi_easy_admin_plus_logout
                target: easyadmin
            form_login:
                login_path: wandi_easy_admin_plus_login
                check_path: wandi_easy_admin_plus_login
                default_target_path: easyadmin
                remember_me: true
                csrf_token_generator: security.csrf.token_manager
        # ...

    access_control:
        - { path: '^/admin/login', role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: '^/admin/', role: ROLE_EASY_ADMIN }
        # ...
```

**Important**: Make sure that no firewall declared before our, does not match with the prefix we use

### Update schema

The bundle uses its own **User** entity. So we need to update your database schema.
```
$ php bin/console doctrine:schema:update -f
```

### Commands

* Create an admin
 ```
 php bin/console wandi:easy-admin-plus:user:create admin password
 ```

* Change admin password
 ```
 php bin/console wandi:easy-admin-plus:user:change-password admin password2
 ```

* Enable an admin
 ```
 php bin/console wandi:easy-admin-plus:user:enable admin
 ```
 
* Disable an admin
 ```
 php bin/console wandi:easy-admin-plus:user:disable admin
 ```
