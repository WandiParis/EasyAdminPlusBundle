
### Edit In place
Use "edit_in_place" in show or list
```yaml
- { property: title, label: title, edit_in_place: true}
```

Work with date, datetime, time, entity and string in show, list and sublist but if you want to create another type you can.

Eip is customable, here an exemple of String edit in place:

Create your class
```php
<?php
class StringEipType extends AbstractEipType{
    
    public function getTemplate(): string{
        return '@EasyAdmin/edit_in_place/_string.html.twig';
    }

    public function getType(): string{
        return 'string'; 
    }
}
``` 

Create your template
```twig
{% if '\n' in valueRaw %}
    <textarea  class=" form-control col-md-12" id="input-{{ id }}">{{ valueRaw }}</textarea>
{% else %}
    <input  class="eap-edit-in-place-input" id="input-{{ id }}" type="text" value="{{ valueRaw }}"/>
{% endif %}
```

The type is calculated but if you want to use your own type use edit_in_place.type
```yaml
- { property: title, label: title, edit_in_place: {'type': 'string'} }
```