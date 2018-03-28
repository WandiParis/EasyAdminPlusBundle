# EasyAdminPlusBundle

### :exclamation: Disclaimer :exclamation: 

**EXPERIMENTAL-BETA VERSION** > not recommended for production use
-------

### About

EasyAdminPlusBundle is a Symfony 4 wrapper for the amazing [EasyCorp/EasyAdminBundle](https://github.com/EasyCorp/EasyAdminBundle) which includes some extra features. 

### Features

- [x] User management to restrict access to the admin area.
- [x] Provide a generator to guess the default [EasyAdmin](https://symfony.com/doc/current/bundles/EasyAdminBundle/book/configuration-reference.html) configuration based on Doctrine Types', Annotations' & Asserts' reflection + support popular 3rd party bundles.
- [x] Add an action to manage translations files.
- [ ] Add an action to export entities content in CSV, XLS, JSON.

### Requirements

* PHP >= 7.1
* Symfony 4
* EasyAdminBundle ^1.17

### Install

```shell
$ composer require wandi/easyadmin-plus-bundle
```

### Getting started

1. [Authentication](doc/chapter-1.md)
2. [Generator](doc/chapter-2.md)
3. [Translation Action](doc/chapter-3.md)
4. [Export Action](doc/chapter-4.md)
