Configuration
=============

You can configure the file extensions and filter patterns for all packages or edit the config
more finely in each package.

### Edit the localized asset

You can edit the config of the locale for each asset.

**Example:**

```yaml
fxp_require_asset:
    locale:
        fr_fr:
            @webpack/asset1.js: @package1/locale/asset1-fr-fr.js
        fr_ca:
            @webpack/asset1.js: @package1/locale/asset1-fr-ca.js
        fr:
            @webpack/asset1.js: @package1/locale/asset1-fr.js
        it:
            @webpack/asset2.js:
                - @webpack/asset1-it-part1.js
                - @webpack/asset1-it-part2.js
```

### Disable Webpack

By default, the Webpack require tag renderer is enabled, but you can disable it:

```yaml
fxp_require_asset:
    webpack:
        enabled: false
```

### Define manually the webpack plugin adapter

By default, the plugin adapter is defined automatically with the `framework.assets.json_manifest_path`
config. If the the `json_manifest_path` option is defined, the `manifest` adapter is used, otherwise,
it's the `assets` adapter that is used.
But you can defined the adapter:

```yaml
fxp_require_asset:
    webpack:
        adapter: 'manifest'
```

### Change the webpack manifest file

By default, the localisation of the webpack manifest json file is retrieved automatically in the
`framework.assets.json_manifest_path` config.
But you can edit the localisation:

```yaml
fxp_require_asset:
    webpack:
        manifest_adapter:
            file: '%kernel.root_dir%/var/my_custom_manifest.json'
```

### Change the webpack assets file

By default, the localisation of the webpack assets json file is in the root
directory of your project and it's named `assets.json`.
But you can edit the localisation:

```yaml
fxp_require_asset:
    webpack:
        assets_adapter:
            file: '%kernel.root_dir%/var/my_custom_assets.json'
```

### Enable the cache of Webpack Assets Adapter

By default, the cache is enabled only in production, and use the `cache.app` cache service. However,
you can enable manually the cache of the webpack manager or change the cache service:

```yaml
fxp_require_asset:
    webpack:
        assets_adapter:
            cache:
                enabled: true
                service_id: 'cache.custom_service_id'
                key: 'custom_key_used_in_cache'
```
