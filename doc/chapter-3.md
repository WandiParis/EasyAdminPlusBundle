# Translator

`EasyAdminPlus` is packaged with an action to manage all your translations files directly in the admin area.

<img src="images/translations-desktop.png" align="center" alt="Translations Desktop" />

### Configuration

```yaml
# config/packages/wandi_easyadmin_plus.yaml
wandi_easy_admin_plus:
    translator:
        # defines the locales you want to manage
        locales:
            - fr
            - en
        # defines the directories in which you want to extract translations files
        paths:
            - /translations
            - ...
        # defines a list of domains you want to exclude from admin
        excluded_domains:
            - validators
            - ...
```

If you don't provide these settings, the `Translator` will extract files located in the default `Symfony 4` translations directory (`/translations`) and only work with your default locale (`locale` parameter or `kernel.default_locale` if not set)

### Loading

Add the `wandi_easy_admin_plus_translations` route in the `menu` attribute of the `EasyAdmin` configuration file.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    site_name: Your website
    design:
        # ...
    menu:
        # ...
        - { route: 'wandi_easy_admin_plus_translations', label: 'Translations', icon: 'globe' }
```

### Translations Screen

Just browse `/admin/translations` or click on your item in the `EasyAdmin` menu.

* On the first part, you can switch the current domain to manage.

<img src="images/translations-desktop-domains.png" align="center" alt="Translations Domains" />

* On the second part, you've a list of all matching files and all the keys (exploded) with translations in the locales you defined.

<img src="images/translations-desktop-translations.png" align="center" alt="Translations files, keys & values" />

* On the last part, you can submit and save your changes by clicking on the sticky bottom bar.

<img src="images/translations-desktop-save.png" align="center" alt="Translations Save" />

The action is also responsive:

<img src="images/translations-mobile.png" align="center" alt="Translations Mobile" />

### Formats

The translator manage all the translations formats supported by Symfony (yaml, xlf, json, ts, php, po, mo, ini, csv) and preserve the original format when committing the changes.

All the files are backuped according to the Symfony convention (eg: `messages.en.xlf~`) before erasing task complete.

### How it works

The `Translator` list all the files in the directories paths provided, it extracts all the files and all the keys from the dictionaries.

- If a file is missing in another locale, it'll be created after submission.
- If some keys are missing in the same file in another locale, it'll be created with empty value after submission.

When submitting the form, files on the current domain are erased and translations cache dir is cleared.

----------

Next chapter: [Chapter 4 - Export Action](chapter-4.md)