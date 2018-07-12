# EasyAdminPlusBundle

### :exclamation: Disclaimer :exclamation: 

### About

EasyAdminPlusBundle is a Symfony 4 wrapper for the amazing [EasyCorp/EasyAdminBundle](https://github.com/EasyCorp/EasyAdminBundle) which includes some extra features. 

### Features

- [x] Provide a generator to guess the default [EasyAdmin](https://symfony.com/doc/current/bundles/EasyAdminBundle/book/configuration-reference.html) configuration based on Doctrine Types', Annotations' & Asserts' reflection + support popular 3rd party bundles.
- [x] Add an action to manage translations files.
- [x] ACL to restrict access based on `entity/action role permissions`
- [x] Add an action to export entities in CSV.
- [x] Filters on entity

### Requirements

* PHP >= 7.1
* Symfony 4
* EasyAdminBundle ^1.17

### Install

```shell
$ composer require lle/easyadmin-plus-bundle
```

### Replace EasyAdmin controller

Load routes from our `AdminController` or yours but make sure it extends `LleEasyAdminPlusBundle` Controller

```yaml
# config/routes/easy_admin.yaml
easy_admin_bundle:
    resource: '@LleEasyAdminPlusBundle/Controller/AdminController.php'
    prefix: /admin
    type: annotation
```

### Getting started

1. [Filter](doc/chapter-6.md)
2. [Generator](doc/chapter-2.md)
3. [Translation Action](doc/chapter-3.md)
4. [ACL](doc/chapter-4.md)
5. [Export Action](doc/chapter-5.md)
6. [Custom Form Types](doc/chapter-7.md)
