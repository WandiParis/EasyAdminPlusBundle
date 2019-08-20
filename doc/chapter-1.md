# Authentication

`EasyAdminPlus` is packaged with an `Authentication` layer which allows you to restrict access to the admin area.

### Configuration
Update your `security` settings: 

```yaml
# config/packages/security.yaml
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

:exclamation: Make sure that no conflicting firewall is declared before ours (a firewall that will match with the prefix we use)

### Update schema

The bundle uses its own **User** entity. So you need to update the database schema.

```shell
$ php bin/console doctrine:schema:update -f
```

### Commands

* Create an admin
 ```shell
 php bin/console wandi:easy-admin-plus:user:create admin password
 ```

 ```shell
 php bin/console wandi:easy-admin-plus:user:create admin password ROLE_EASY_ADMIN_1 ROLE_EASY_ADMIN_2
 ```
 
 * Remove an admin
  ```shell
  php bin/console wandi:easy-admin-plus:user:remove admin
  ```
  
 * Add roles to an admin
  ```shell
  php bin/console wandi:easy-admin-plus:user:add-roles admin ROLE_EASY_ADMIN_1 ROLE_EASY_ADMIN_2
  ```
  
* Remove roles from an admin
```shell
php bin/console wandi:easy-admin-plus:user:remove-roles admin ROLE_EASY_ADMIN_1 ROLE_EASY_ADMIN_2
```

* Set roles of an admin
```shell
php bin/console wandi:easy-admin-plus:user:set-roles admin ROLE_EASY_ADMIN_1 ROLE_EASY_ADMIN_2
```
 
* Change admin password
 ```shell
 php bin/console wandi:easy-admin-plus:user:change-password admin password2
 ```

* Enable an admin
 ```shell
 php bin/console wandi:easy-admin-plus:user:enable admin
 ```
 
* Disable an admin
 ```shell
 php bin/console wandi:easy-admin-plus:user:disable admin
 ```
 
 ### Login Screen
 
 The login `form` get the `site_name` property from `EasyAdmin` configuration.
 
 <p align="center">
    <img src="images/login.png" align="middle" alt="Login Form" />
  </p>
 
 ### Fixtures
 
 :exclamation: If you're using `DataFixtures` in your project, to avoid admin's lost, we recommend you to add a `LoadAdmin` Fixtures that uses the previous command.

```php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Container;

class EasyAdminFixtures extends Fixture
{
    /** @var Container $container */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $application = new Application($this->container->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'wandi:easy-admin-plus:user:create',
            'username' => 'admin',
            'password' => '5K48pDgZveZT',
            'roles' => [
                'ROLE_EASY_ADMIN_GOD',
            ],
        ]);
        $output = new NullOutput();

        $application->run($input, $output);
    }
}

```

----------

Next chapter: [Chapter 2 - Generator](chapter-2.md)