Inline asset usage
==================

For rendering automatically all javascripts and stylesheets of all block, you must
used the twig functions:

- `inlineStylesheetsPosition()` in global html stylesheet template
- `inlineJavascriptsPosition()` in global html javascript template
- `renderAssets()` in the end of tempalte

The `renderAssets()` can replace the tag position of assets
(`inlineStylesheetsPosition()` and `inlineJavascriptsPosition()`) by the contents
of each asset. This is for this reason that it must be executed last.

**Twig example:**

```html+jinja
<html>
    <head>
        <style type="text/css">
        {{ inlineStylesheetsPosition() }}
        </style>
    </head>
    <body>
        {% set foo: 'bar' %}

        {% inline_javascript %}
            <script>
            alert("Foo: {{ foo }}");
            </script>
        {% endinline_javascript %}

        <script type="text/javascript">
            $( document ).ready( function() {
                {{ inlineJavascriptsPosition() }}
            });
        </script>
    </body>
<html>
{{ renderAssets() }}
```
