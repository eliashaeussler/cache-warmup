# Configuration

The whole cache warmup process can be customized with a various
set of configuration options. Read more about how to provide those
configuration options on this page.

## Config adapters

Each configuration option can be set by a dedicated *config adapter*.
At the moment, the following adapters are supported:

1. [Configuration file](#configuration-file)
2. [Command parameters](#command-parameters)
3. [Environment variables](#environment-variables)

::: warning IMPORTANT

Config adapters are loaded from **top to bottom**. That means, adapters
which are loaded later receive **higher priority** and may
**override configuration options** provided by previously loaded config
adapters.

:::

## Configuration file

You can provide configuration files in the following formats:

* [JSON](#json-and-yaml)
* [PHP](#php)
* [YAML](#json-and-yaml)

### JSON and YAML

For JSON and YAML files, the name of each configuration option
can be looked up in the [configuration reference](config-reference/index.md).
It must be written in *lowerCamelCase*, e.g. the
[`--crawler-options`](config-reference/crawler-options.md) command
parameter must be configured as `crawlerOptions`:

::: code-group

```json [JSON]
{
    "sitemaps": [
        "https://www.example.org/sitemap.xml"
    ],
    "exclude": [
        "*foo*"
    ]
}
```

```yaml [YAML]
sitemaps:
  - https://www.example.org/sitemap.xml
exclude:
  - '*foo*'
```

:::

#### JSON schema

To make it easier for you to compose your configuration files,
you can reference the available [JSON schema](../res/cache-warmup-config.schema.json)
in your JSON file and enable autocompletion in your IDE:

```json{2}
{
    "$schema": "https://cache-warmup.dev/config.schema.json",
    "sitemaps": [
        "https://www.example.org/sitemap.xml"
    ],
    "exclude": [
        "*foo*"
    ]
}
```

### PHP

PHP configuration files must return a closure which receives the
current instance of
[`Config\CacheWarmupConfig`](../src/Config/CacheWarmupConfig.php).
They may also return a (new) instance to override the current one:

```php
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addSitemap(
        CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/sitemap.xml'),
    );
    $config->addExcludePattern(
        CacheWarmup\Config\Option\ExcludePattern::create('*foo*'),
    );

    return $config;
};
```

## Command parameters

The library accepts a various set of command parameters. Each
command parameter reflects an available configuration option:

```bash
./cache-warmup.phar "https://www.example.org/sitemap.xml" --exclude "*foo*"
```

In addition, the [`--config`](config-reference/config.md) option allows to provide the path to a
[configuration file](#configuration-file):

```bash
./cache-warmup.phar --config cache-warmup.json
```

All available command parameters are reflected in the
[configuration reference](config-reference/index.md). You can also run the command with `--help`
to see a detailed explanation of all available command parameters.

## Environment variables

Several configuration options can also be configured using environment
variables. Each environment variable is prefixed with `CACHE_WARMUP_`,
followed by the configuration option in *SCREAMING_SNAKE_CASE*.

Example:

* The [`sitemaps`](config-reference/sitemaps.md) option is expected as `CACHE_WARMUP_SITEMAPS`
* The [`crawlerOptions`](config-reference/crawler-options.md) option is expected
  as `CACHE_WARMUP_CRAWLER_OPTIONS`

### Value mapping

The following value transformation between environment variables and
configuration options exists:

**Lists:** Values must be separated by comma:

```bash
CACHE_WARMUP_SITEMAPS="https://www.example.org/sitemap.xml, /var/www/html/sitemap.xml"
```

**Booleans:** Values matching `true`, `yes` or `1` are interpreted as `true`:

```bash
CACHE_WARMUP_PROGRESS="true"
CACHE_WARMUP_PROGRESS="yes"
CACHE_WARMUP_PROGRESS="1"
```

**All other values** are converted to their expected type:

```bash
# Internally converted to an integer value
CACHE_WARMUP_LIMIT="50"
```
