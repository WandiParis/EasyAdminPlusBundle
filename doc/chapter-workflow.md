# Workflow display

You can automatically display a worflow field that handle the transition actions.

### Settings

To enable the workflow template just add the template to your field.

It will be displayed as a dropdown of all valid transition. The link execute the transition and come back to the referer.

By default, a modal is used to confirm the action. To disactive modal :
- add parameter "confirm" to false for the render field
- add parameter "wf_confirm" to disactive for all renders

Work on list or show action

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Controle:
            wf_confirm: false
            class: App\Entity\Controle
            list:
                fields:
                    - { property: etat, label: label.etat, template: '@LleEasyAdminPlus/default/field_workflow.html.twig', confirm: false }

```


[Back to main readme](../README.md)
