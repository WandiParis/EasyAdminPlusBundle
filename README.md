:exclamation: **DISCLAIMER** :exclamation: 

EXPERIMENTAL-BETA VERSION not recommended for production use

# EasyAdminPlusBundle

EasyAdminPlusBundle is a Symfony4 wrapper for the amazing [EasyCorp/EasyAdminBundle](https://github.com/EasyCorp/EasyAdminBundle). 

It includes some extra features such as:
* User management to restrict access to the admin area.
* Provide a generator to guess the default EasyAdmin configuration based on Doctrine Types', Annotations' & Asserts' reflection and support popular 3rd party bundles.
* Add an action to manage translations files.
* Add an action to export entities content in CSV.

### Requirements

* PHP >= 7.1
* Symfony 4
* EasyAdminBundle ^1.17

### Install
```
$ composer require wandi/easyadmin-plus-bundle
```

### Book
1. [Authentication](doc/chapter-1.md)
2. [Generator](doc/chapter-2.md)
3. [Translation Action](doc/chapter-3.md)
4. [Export Action](doc/chapter-4.md)
