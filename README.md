# JSON Forms for Drupal

JSON Forms for Drupal is an implementation of the [JSON Forms](
https://jsonforms.io/) specification for Drupal.

## Additional features and keywords

In this implementation there are some custom features and keywords not
specified in standard JSON Forms. (TODO: Not all additional possibilities are
described here, yet.)

### Array control: options `itemLayout` and `elements`

There are two additional options in controls referencing an array.

The option `itemLayout` allows to specify the layout for the individual controls
of an array item (e.g. `VerticalLayout`). The default is `TableRow`.

The option `elements` allows to specify the controls for the properties of an
array item. By default a control is generated for each item property.

Example:
```json
{
  "itemLayout": "VerticalLayout",
  "elements": [
    {
      "type": "Control",
      "label": "Name",
      "scope": "#/properties/name"
    },
    {
      "type": "Control",
      "label": "Birth Date",
      "scope": "#/properties/birthDate"
    }
  ]
}
```

### Description display

The Keyword `descriptionDisplay` in Control options allows to specify the
display of the description. Possible options:

* `after`
* `before`
* `invisible`
* `tooltip`

The first three options are standard options available for the
`#description_display` in Drupal.

The option `tooltip` leads to an additional CSS class on the description
element: `json-forms-description-tooltip`. This can be used to process it
with another module to display the description as tooltip.

With the module [Form Tips](https://www.drupal.org/project/formtips) it can
be achieved with this CSS selector:

```css
:not(.json-forms-description-tooltip)
```

## Limitations

Some things cannot be done with (standard) Drupal forms, e.g.
[Rules](https://jsonforms.io/docs/uischema/rules/) cannot completely be mapped
to [conditional form fields](https://www.drupal.org/docs/drupal-apis/form-api/conditional-form-fields).

The [`detail` option](https://jsonforms.io/docs/uischema/controls#the-detail-option)
for controls referencing an array is unsupported.

TODO: Describe all limitations.
