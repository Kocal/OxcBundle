# OxcBundle

[![.github/workflows/ci.yaml](https://github.com/Kocal/OxcBundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/Kocal/OxcBundle/actions/workflows/ci.yaml)
[![Packagist Version](https://img.shields.io/packagist/v/kocal/oxc-bundle)](https://packagist.org/packages/kocal/oxc-bundle)

A Symfony Bundle to easily download and use [Oxlint](https://oxc.rs/docs/guide/usage/linter.html) and [Oxfmt](https://oxc.rs/docs/guide/usage/formatter.html) 
(from the [Oxc project](https://oxc.rs/)) in your Symfony applications, to lint your front assets without needing Node.js (ex: when using [Symfony AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html)).

> [!TIP]
> If you prefer to use Biome.js instead, check [Kocal/BiomeJsBundle](https://github.com/Kocal/BiomeJsBundle)!

---

## Installation

Install the bundle with Composer:

```shell
composer require kocal/oxc-bundle --dev
```

If you use [Symfony Flex](https://symfony.com/doc/current/setup/flex.html), everything must be configured automatically.
If that's not the case, please follow the next steps:

<details>
<summary>Manual installation steps</summary>

1. Register the bundle in your `config/bundles.php` file:

```php
return [
    // ...
    Kocal\OxcBundle\KocalOxcBundle::class => ['dev' => true],
];
```

2. Create the configuration file `config/packages/kocal_oxc.yaml`:

```yaml
when@dev:
    kocal_oxc:
        # The Oxc apps version to use, that you can find at https://github.com/oxc-project/oxc/tags,
        # it follows the pattern "apps_v<apps_version>"
        apps_version: '1.43.0'

```

3. Create the recommended `.oxlintrc.json` file at the root of your project:

```json
{
    "plugins": null,
    "categories": {},
    "rules": {},
    "env": {
        "builtin": true
    },
    "globals": {},
    "ignorePatterns": [
        "assets/vendor/**",
        "assets/controllers.json",
        "public/assets/**",
        "public/bundles/**",
        "tests/**",
        "var/**",
        "vendor/**",
        "composer.json",
        "package.json"
    ]
}
```

4. Create the recommended `.oxfmtrc.json` file at the root of your project:

```json
{
    "ignorePatterns": [
        "assets/vendor/**",
        "assets/controllers.json",
        "public/assets/**",
        "public/bundles/**",
        "tests/**",
        "var/**",
        "vendor/**",
        "composer.json",
        "package.json"
    ]
}
```

</details>

## Configuration

The bundle is configured in the `config/packages/kocal_oxc.yaml` file:

```yaml
when@dev:
    kocal_oxc:
        # The Oxc apps version to use, that you can find at https://github.com/oxc-project/oxc/tags,
        # it follows the pattern "apps_v<apps_version>"
        apps_version: '1.43.0'

```

## Usage

### `oxc:download:oxlint`

Download Oxlint for your configured version and for your platform.

By default, the command will download the binary in the `bin/` directory of your project.

```shell
php bin/console oxc:download:oxlint
bin/oxlint --version

# or, with a custom destination directory
php bin/console oxc:download:oxlint path/to/bin
path/to/bin/oxlint --version
```

### `oxc:download:oxfmt`

Download Oxfmt for your configured version and for your platform.

By default, the command will download the binary in the `bin/` directory of your project.

```shell
php bin/console oxc:download:oxfmt
bin/oxfmt --version

# or, with a custom destination directory
php bin/console oxc:download:oxfmt path/to/bin
path/to/bin/oxfmt --version
```

## Inspirations

- https://github.com/SymfonyCasts/tailwind-bundle
- https://github.com/Kocal/BiomeJsBundle
