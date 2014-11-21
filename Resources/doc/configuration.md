Configuration
=============

You can configure the file extensions and filter patterns for all packages or edit the config
more finely in each package.

### Edit the default configuration for all packages

#### Edit the default extensions configuration

All default extensions must be added to the section `fxp_require_asset.default.extensions` with
the name of the file extension as a key, and an associative array as a value.

**Parameters available:**

| Name        | Type   | Default value | Description                                                       |
|-------------|--------|---------------|-------------------------------------------------------------------|
| `filters`   | array  | `[]`          | The assetic formulae filters                                      |
| `options`   | array  | `[]`          | The assetic formulae options (debug is not used)                  |
| `extension` | string | `null`        | The output file extension, by default, the current file extension |
| `debug`     | bool   | `false`       | Include the file extension only in debug mode                     |
| `exclude`   | bool   | `false`       | Exclude the file extension                                        |

**Example:**

```yaml
fxp_require_asset:
    default:
        extensions:
            md:
                exclude: true
```

#### Override the default extensions configuration

The parameter `fxp_require_asset.default.replace_extensions` (`bool`) allows to override the
default configuration of file extension by your custom configuration of file extension (not merged).

**Example:**

```yaml
fxp_require_asset:
    default:
        replace_extensions: true
```

By default, Only files whose extension is compatible with the internet browsers are copied:

- `map` (only when assetic debug is active)
- `js`
- `css`
- `eot`
- `svg`
- `ttf`
- `woff`
- `jpg`
- `jpeg`
- `png`
- `webp`
- `mp3`
- `aac`
- `wav`
- `ogg`
- `webm`
- `mp4`
- `ogv`

#### Edit the default patterns configuration

All the filter patterns for the assets (in addition to the file extensions allowed), must
be added to the `fxp_require_asset.default.patterns` section.

The pattern must be at `Glob` format.
The `!` allow you to exclude all assets matched by the pattern.

**Example:**

```yaml
fxp_require_asset:
    default:
        patterns:
            - "*"
            - "!test/*"
```

This example will copy all files whose the file extension is allowed, but to exclude the folder
`test`.

### Edit the configuration for one package

The package config must be added to the `fxp_require_asset.packages.{package_name}` section (see
[Special Configuration](configuration.md#special-configuration) for formatting package names).

**Parameters available:**

| Name                         | Type   | Default value | Description                                    |
|------------------------------|--------|---------------|------------------------------------------------|
| `source_path` °              | string | `null`        | The source path of the package                 |
| `source_base` °°             | string | `null`        | The source base path of the package            |
| `replace_default_extensions` | bool   | `false`       | Override all default config for file extension |
| `replace_default_patterns`   | bool   | `false`       | Override all default config for filter pattern |
| `extensions`                 | array  | `[]`          | The map of file extension config               |
| `patterns`                   | array  | `[]`          | The list of filter patterns                    |

> ° The parameter is required if the package is not a Bower or NPM package or a Symfony Bundle

> °° Is not necessary in most cases

**Example:**

```yaml
fxp_require_asset:
    packages:
        custom_package:
            source_path: "%kernel.root_dir%/../vendor/foo/bar"
```

#### Edit the extensions configuration for one package

All the file extensions for the assets must be added to the
`fxp_require_asset.packages.{package_name}.extensions` section.

The configuration is identical to the default configuration (see
[Edit the default extensions configuration](configuration.md#edit-the-default-extensions-configuration)).

**Example:**

```yaml
fxp_require_asset:
    packages:
        acme_demo_bundle:
            extensions:
                md:
                    exclude: true
```

#### Edit the patterns configuration for one package

All the filter patterns for the assets (in addition to the file extensions allowed), must
be added to the `fxp_require_asset.packages.{package_name}.patterns` section.

The configuration is identical to the default configuration (see
[Edit the default patterns configuration](configuration.md#edit-the-default-patterns-configuration)).

**Example:**

```yaml
fxp_require_asset:
    packages:
        acme_demo_bundle:
            patterns:
                - "*"
                - "!test/*"
```

This example will copy all files whose the file extension is allowed, but to exclude the folder
`test`.

Parameter `fxp_require_asset.packages.{package_name}.replace_default_patterns` (`bool`) allows to
override the default configuration of file extension by the custom package configuration.

**Example:**

```yaml
fxp_require_asset:
    packages:
        acme_demo_bundle:
            replace_default_patterns: true
```

#### Edit the common assets configuration

You can create common assets, which are reality the assetic formulae dedicated to the require
assets.

**Example:**

```yaml
fxp_require_asset:
    common_assets:
        common_asset_js_name:
            output: "/common.js"
            filters: [?closure]
            options: { debug: false }
            inputs:
                - @asset/source/path/file1.js
                - @asset/source/path/file2.js
```

In `debug` mode, the list of the require assets will be added to template with each link for
each asset. In contrast, in `prod` mode, only the link defined in the `output` parameter is
added.

Of course, any asset included in the asset commons will not be added twice if the template
also required a specific asset.

### Rewrite the output path of asset files

You can completely change the target path of each asset via a list of `Glob` pattern. In this way,
you master the directory of the asset, but also his name, as her name of file extension.

This is in the `fxp_require_asset.output_rewrites` section. Beware, each pattern will be executed
in order for each file.

See [Special Configuration](configuration.md#special-configuration) for formatting public output of
package names).

**Example:**

```yaml
fxp_require_asset:
    output_rewrites:
        "*/less/*": "*/css/*"
```

This example will rename all directories `/less/` to `/css/`.

#### Using variables in the output pattern

You can use the ordered variables in the pattern of public output, if your pattern rewriting using
the wildcard (`*`). However, The wildcards used in first and last position are not considered.

**Example:**

```yaml
fxp_require_asset:
    output_rewrites:
        "acmedemo/*/*.css": "css/$1.css"
```

In this example, the variable `$0` is the folder name, and the variable `$1` is the file name.

### Rewrite the url reference in the CSS assets

By default, Assetic has the filter `cssrewrite` to do this work, but alas, it may not be compatible
with the system of rewriting the public output of the asset.

For get the good path of the asset, you must use the filter `requirecssrewrite`.

**Example of configuration for used the Require Css Rewrite Filter:**

```yaml
fxp_require_asset:
    default:
        extensions:
            css: { filters: [requirecssrewrite] }
```

### Change the output prefix

By default, the assets are copied to two different folders depending on the debug mode:

- `assets` for the prod mode
- `assets-dev` for the debug mode

But you can change the values with the following settings:

```yaml
fxp_require_asset:
    output_prefix:       "custom-assets"
    output_prefix_debug: "custom-assets-dev"
```

### Change the composer installed path

You can manually define composer installed file with the parameter `fxp_require_asset.composer_installed_path`.

### Change the project base directory

You can manually define the project root with the parameter `fxp_require_asset.base_dir`.

Special configuration
---------------------

### Package provided by NPM/Bower Dependency Manager for Composer

The package name is formatted in this way:

- **NPM:** `@npm/{npm-package-name}`
- **Bower:** `@bower/{bower-package-name}`

However, the assets of each packages will be copied directly to the folder formed with only
the name of the asset package.

**Example:** `web/assets/{asset-package-name}/`

### Package provied by Symfony Bundle

The package name is formatted using the name of the complete name of bundle, separated by
underscores, with the lowercase characters.

**Example:** `acme_demo_bundle` for `AcmeDemoBundle`

However, the assets of each Bundles will be copied directly to the folder formed with only
the name of the bundle, with lower character, without `Bundle` at the end.

**Example:** `web/assets/acmedemo/` for `AcmeDemoBundle`

By default, all assets defined in the `Resources` directory of Bundle are copied in the
assetic public directory, except of the sub-directories below:

- `config`
- `doc`
- `license`
- `licenses`
- `meta`
- `public`
- `skeleton`
- `views`
