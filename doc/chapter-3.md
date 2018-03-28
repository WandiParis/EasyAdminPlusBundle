# Translator

`EasyAdminPlus` is packaged with an action to manage all your translations files directly in the admin area.

<p align="center">
    <img src="images/translations-desktop.png" align="center" alt="Translations Desktop" />
</p>

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

All the settings are optional.

If you don't provide them, the `Translator` will extract files located in the default `Symfony 4` translations directory (`/translations`) and only work with your default locale (`locale` parameter or `kernel.default_locale` if not set)

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

#### 1. First part

You can switch the current domain to manage.

<p align="center">
    <img src="images/translations-desktop-domains.png" align="center" alt="Translations Domains" />
</p>

-------

#### 2. Middle part

You've a list of:
* all matching files (if you're using several files formats for the same domain), note that we only display file extension.
* all the keys (`action.save` is exploded into `action > save` for user readability) 
* all the translations values in all the locales you defined.

*Eg: several files*
<p align="center">
    <img src="images/translations-desktop-translations-files.png" align="center" alt="Translations files, keys & values" />
</p>

-------

*Eg: classic case with only one file*
<p align="center">
    <img src="images/translations-desktop-translations.png" align="center" alt="Translations files, keys & values" />
</p>

-------

**The grid uses a `display: flex` layout, so every translation value will be floated.** 

#### 3. Last part

You can submit and save your changes by clicking on the button located in the sticky bottom bar.

<p align="center">
    <img src="images/translations-desktop-save.png" align="center" alt="Translations Save" />
</p>

### Translations Screen - Mobile

The etire action is also responsive:

<p align="center">
    <img src="images/translations-mobile.png" align="center" alt="Translations Mobile" />
</p>

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