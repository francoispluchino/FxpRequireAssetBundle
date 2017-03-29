Fxp RequireAssetBundle
======================

[![Latest Version](https://img.shields.io/packagist/v/fxp/require-asset-bundle.svg)](https://packagist.org/packages/fxp/require-asset-bundle)
[![Build Status](https://img.shields.io/travis/fxpio/fxp-require-asset-bundle/master.svg)](https://travis-ci.org/fxpio/fxp-require-asset-bundle)
[![Coverage Status](https://img.shields.io/coveralls/fxpio/fxp-require-asset-bundle/master.svg)](https://coveralls.io/r/fxpio/fxp-require-asset-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fxpio/fxp-require-asset-bundle/master.svg)](https://scrutinizer-ci.com/g/fxpio/fxp-require-asset-bundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/6819d453-7c5c-447f-ba5d-58e25409ac2d.svg)](https://insight.sensiolabs.com/projects/6819d453-7c5c-447f-ba5d-58e25409ac2d)

The Fxp RequireAssetBundle is a helper for twig to manage automatically the required assets
with Assetic. It allows to define the required assets (script, style) directly in the
Twig template and adds the HTML links of the assets automatically to the right place in
the template, while removing duplicates. The bundle retrieves automatically asset dependencies
defined by [fxp/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin),
or by natives NPM and Bower and adds automatically the assets in the Assetic manager.

The configuration included in the bundles can be overloaded in the global configuration,
in this way, even if a Twig template uses a specific asset, and your global config changes
the URL output of assets (output rewriting), the Twig template will automatically include
the correct URL of asset, without your having to overload the Twig template.

##### Features include:

- All features of [Fxp Require Asset](https://github.com/fxpio/fxp-require-asset)
- Automatically copy all assets:
  - of NPM/Bower packages by Fxp Composer Asset Plugin or natives NPM and Bower in the assetic public directory
  - defined in resources section of a Symfony2 Bundle (except `config`, `doc`, `license(s)`, `meta`, `public`, `skeleton`, `views`)
- Automatic addition of localized commons assets
- Compiling the final list of asset in cache for increase performance
- Updating the list of assets when the source files requires a cache refresh (`dev` mode)
- Native support of the assetic manager (command `assetic:dump` and controller)
- Native support of the symfony templating (base URL and version)
- Assetic filters:
  - `parameterbag`: for replace the symfony parameters in assets
  - `lessvariable`: for inject the asset package paths as variables
- Configure (in global config or in container compiler pass):
  - the assetic filters of asset package by the extensions
  - the assetic filters for all asset packages
  - the custom asset package
  - the rewrite output path of asset
  - the common assets (assetic formulae dedicated to the require assets)
  - the locale asset defined by each asset and/or common assets
  - the locale common assets (automatic, but can be overridden)
  - the replacement of assets by other assets

Documentation
-------------

The bulk of the documentation is located in the `Resources/doc/index.md`:

[Read the Documentation](Resources/doc/index.md)

[Read the Release Notes](https://github.com/fxpio/fxp-require-asset-bundle/releases)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

[LICENSE](LICENSE)

About
-----

Fxp RequireAssetBundle is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/fxpio/fxp-require-asset-bundle/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/fxpio/fxp-require-asset-bundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
