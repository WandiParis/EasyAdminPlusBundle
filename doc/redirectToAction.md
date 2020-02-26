# Redirect to action

Now you can decide which action you want to redirect to for edit and new action.

Redirection are available for the following actions:
* edit
* new

You can redirect to:
* edit
* new
* show

## Usage
```yaml
            new:
                title: title.myEntity.new
                actions: []
                redirectToAction: edit
                fields:
                    - { property: myProperty, label: field.myProperty }
                ...    
```
The same syntax goes for the edit action.
