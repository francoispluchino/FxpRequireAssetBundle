Getting Started
===============

## Prerequisites

This version of the bundle requires Symfony 2.4+.

## Installation

Installation is a quick, 2 step process:

1. Download the bundle using composer
2. Enable the bundle
3. Configure the bundle (optional)

### Step 1: Download the bundle using composer

Add Fxp RequireAssetBundle in your composer.json:

```js
{
    "require": {
        "fxp/require-asset-bundle": "1.0@dev"
    }
}
```

Or tell composer to download the bundle by running the command:

```bash
$ php composer.phar require fxp/require-asset-bundle:1.0@dev
```

Composer will install the bundle to your project's `vendor/fxp` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Fxp\Bundle\RequireAssetBundle\FxpRequireAssetBundle(),
    );
}
```

### Step 3: Configure the bundle (optional)

You can override the default configuration adding `fxp_require_asset` tree in `app/config/config.yml`.
For see the reference of Fxp Require Asset Configuration, execute command:

```bash
$ php app/console config:dump-reference FxpRequireAssetBundle
```

### Next Steps

Now that you have completed the basic installation and configuration of the
Fxp RequireAssetBundle, you are ready to learn about usages of the bundle.

The following documents are available:

- [Custom configuration](configuration.md)
- [Basic usage](usage_basic.md)
- [Require asset usage in Twig](https://github.com/francoispluchino/fxp-require-asset/blob/master/Resources/doc/usage_twig_require_asset.md)
- [Inline asset usage in Twig](https://github.com/francoispluchino/fxp-require-asset/blob/master/Resources/doc/usage_twig_inline_asset.md)
