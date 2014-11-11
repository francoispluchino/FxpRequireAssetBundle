Inline asset usage
==================

For rendering automatically all scripts and styles of all block, you must
used the twig functions:

- `inlineStylesPosition()` in global html style template
- `inlineScriptsPosition()` in global html script template
- `renderAssetTags()` in the end of tempalte

The `renderAssetTags()` can replace the tag position of assets
(`inlineStylesPosition()` and `inlineScriptsPosition()`) by the contents
of each asset. This is for this reason that it must be executed last.

**Twig example:**

```html+jinja
<html>
    <head>
        <style type="text/css">
        {{ inlineStylesPosition() }}
        </style>
    </head>
    <body>
        {% set foo: 'bar' %}

        {% inline_script %}
            <script>
            alert("Foo: {{ foo }}");
            </script>
        {% endinline_script %}

        <script type="text/javascript">
            $( document ).ready( function() {
                {{ inlineScriptsPosition() }}
            });
        </script>
    </body>
<html>
{{ renderAssetTags() }}
```
