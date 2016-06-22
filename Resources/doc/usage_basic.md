Basic usage
===========

### Usage with NPM/Bower Dependency Manager for Composer

If you used the Composer plugin [fxp/composer-asset-plugin]
(https://github.com/fxpio/composer-asset-plugin), you will have no configuration
to do, because all asset packages are automatically added to the Asset Manager with the
default values.

Of course, you can change the config, see [Configuration](configuration.md).

See [Special configuration](configuration.md#package-provided-by-npmbower-dependency-manager-for-composer).

### Usage with Symfony Bundle

You will have no configuration to do, because all the Symfony Bundles registered in the
`AppKernel` will be automatically added to the Asset Manager with the default values.

Of course, you can change the config, see [Configuration](configuration.md).

See [Special configuration](configuration.md#package-provied-by-symfony-bundle).

### Usage with custom package

To manually add a package in the manager, simply add the package configuration (see
[Configuration](configuration.md)) by specifying the essential `source_path` parameter.

**Characters allowed for package name:** `a-z`, `0-9`,  `-`, `_`
