Fxp RequireAssetBundle
======================

[![Latest Stable Version](https://poser.pugx.org/fxp/require-asset-bundle/v/stable.svg)](https://packagist.org/packages/fxp/require-asset-bundle)
[![Latest Unstable Version](https://poser.pugx.org/fxp/require-asset-bundle/v/unstable.svg)](https://packagist.org/packages/fxp/require-asset-bundle)
[![Build Status](https://travis-ci.org/francoispluchino/FxpRequireAssetBundle.svg)](https://travis-ci.org/francoispluchino/FxpRequireAssetBundle)
[![Coverage Status](https://coveralls.io/repos/francoispluchino/FxpRequireAssetBundle/badge.png)](https://coveralls.io/r/francoispluchino/FxpRequireAssetBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francoispluchino/FxpRequireAssetBundle/badges/quality-score.png)](https://scrutinizer-ci.com/g/francoispluchino/FxpRequireAssetBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6819d453-7c5c-447f-ba5d-58e25409ac2d/mini.png)](https://insight.sensiolabs.com/projects/6819d453-7c5c-447f-ba5d-58e25409ac2d)

The Fxp RequireAssetBundle is a manager for the required assets. It allows to define the
required assets (javascript, stylesheet) directly in the Twig template and adds the HTML
links of the assets automatically to the right place in the template, while removing
duplicates. The bundle retrieves automatically asset dependencies defined by
[fxp/composer-asset-plugin](https://github.com/francoispluchino/composer-asset-plugin)
and adds automatically the assets in the Assetic manager.

##### Features include:

- Automatically copy all assets:
  - of NPM/Bower packages in the assetic public directory
  - defined in resources section of a Symfony2 Bundle (except `config`, `doc`, `license(s)`, `meta`, `public`, `skeleton`, `views`)
- Filter the copy of the assets of each packages by:
  - file extensions (and debug mode)
  - glob patterns
- Compiling the final list of asset in cache for increase performance
- Updating the list of assets when the source files requires a cache refresh (`dev` mode)
- Native support of the assetic manager (command `assetic:dump` and controller)
- Configure (in global config or in container compiler pass):
  - the assetic filters of asset package by the extensions
  - the assetic filters for all asset packages
  - the custom asset package
  - the rewrite output path of asset
- Assetic filters:
  - `requirecssrewrite`: for rewrite the url of another require asset in css file
- Automatically move all inline:
  - javascript in the same place defined in the twig base template
  - stylesheet in the same place defined in the twig base template

Documentation
-------------

The bulk of the documentation is located in the `Resources/doc/index.md`:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Fxp RequireAssetBundle is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/francoispluchino/FxpRequireAssetBundle/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/francoispluchino/FxpRequireAssetBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
