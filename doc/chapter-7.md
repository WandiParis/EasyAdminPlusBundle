# Custom Form Types

`EasyAdminPlus` provides custom form types.

-------
## Url Autocomplete Type

This type allows you to make an autocomplete field like the easyadmin's one, but with customizable URL meaning that you can get autocomplete data from other websites/APIs with AJAX calls. This uses the JQuery's select2.

-------
### How to use it

Configure the the property you want to autocomplete in easyadmin YAML:
```yaml
- { property: 'my_property', type: 'Lle\EasyAdminPlusBundle\Form\Type\UrlAutocompleteType', type_options: {'url': 'https://core.nathyslog.com/service.core/services-json', 'value_filter':'service_name'} }
```

In type options, you have:
- url: Required. An url where you get JSON data for the autocomplete.
- value_filter: Optional. A Twig filter to apply to the **current** value of the entity, in case of edition. This is useful if the value is some ID to make it readeable.
- params: Optional params passed in the route (EX: params: {'namequeryparams': '#nameform_namefield'} }) and in controller: $myparams = $request->query->get('params',[])['namequeryparams'] ?? null;
- path: generate an url with path.route and path.params
- placeholder
- class: generate the url for a class
-------
### What url to use ?

EasyAdmin already provides autocomplete URLs for the configured entities, and those can be sufficient most of the time.
Their pattern is: https://www.example.com/admin/?action=autocomplete&entity=`Entity`

Note that "admin" can be named something else in some projects.

If you need to make more specific researches that easyadmin can't provide, you need to make your own autocomplete route.
It has to return JSON.

Request queries are: (in GET)
- query: the current term the user has typed
- page: the requested page number. If none, it's the 1st page. Note that page 1 will not be requested as it's default, but the others will.

Result is an array composed of:
- results: an array of results. Each result is an array of id and text, id is the value that will be saved in database and text is the value that will be showed in the select.
- has_next_page: a boolean. If true, then there are other pages, otherwise false. If you don't use pagination, you can just send false.

Example:
```json
{
    "results":
        [
            {
                "id": "161",
                "text": "Something to select"
            },
            {
                "id": "314",
                "text": "Something else"
            }
        ],
    "has_next_page": false
}
```


-------
[Back to main readme](../README.md)
