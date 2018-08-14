# Batch actions

Batch actions are actions triggered on a set of selected objects.

### Settings

To enable batch actions, you've to add a new node (`batchs`) for the action `list` of the entity.

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Controle:
            class: App\Entity\Controle
            disabled_actions: ['new']
            role: ROLE_CONTROLE
            list:
                title: title.echeance.list
                batchs:
                    - { name: delete, icon: trash, label: label.delete, service: lle.service.delete_batch }
                actions:
                    - { name: show, icon: search }
                    - { name: edit, icon: edit }
                    - { name: delete, icon: trash }
```
On the list, checkbox inputs are added toselect items and buttons are visible on bottom of the list.

<p align="center">
    <img src="images/batch-list.png" align="center" alt="Batch list" />
</p>

-------

### Default behavior

Button are enable only if items are selected

----------

[Back to main readme](../README.md)
