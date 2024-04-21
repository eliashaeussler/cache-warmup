# Configuration Reference

The following pages list all available configuration options.

Each configuration option can only be used when running cache warmup from
the command line. When [using the library with PHP](../api/index.md), only a
limited set of configuration options is available.

::: tip
Read [Configuration](../configuration.md) to get a quick overview about
how to pass configuration options to the library.
:::

::: details Legend
The configuration reference provides various attributes for each
configuration option:

| Attribute                                 | Description                                                                                                                                |
|-------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------|
| 🐝&nbsp;Default                           | Default value of the configuration option, used if no explicit value is configured                                                         |
| 📚&nbsp;Multiple&nbsp;values&nbsp;allowed | Allows multiple values for the configuration options                                                                                       |
| 📝&nbsp;Name                              | Internal name of the configuration option, especially necessary for [JSON and YAML](../configuration.md#json-and-yaml) configuration files |
| 🖥️&nbsp;Option                           | Name of the [command parameters](../configuration.md#command-parameters) used to define the configuration option                           |
| 🚨&nbsp;Required                          | Requires an explicit value for the configuration option                                                                                    |
| 🎨&nbsp;Type                              | Expected type of a special configuration option value in PHP notation                                                                      |
:::

## Input

Lists all configuration options used to determine a list of URLs to be
warmed up.

* [Sitemaps](sitemaps.md)
* [URLs](urls.md)
* [Exclude patterns](exclude.md)
* [Limit](limit.md)
* [Configuration file](config.md)

## Output

Defines how to format cache warmup progress and result.

* [Format](format.md)
* [Progress](progress.md)
* [Endless mode](repeat-after.md)

## Crawling

Describes options to modify and customize the crawling behavior.

* [Crawler](crawler.md)
* [Crawler options](crawler-options.md)
* [Crawling strategy](strategy.md)

## Logging & Error Handling

Describes possibilities to handle and debug errors during cache warmup.
process.

* [Log file](log-file.md)
* [Log level](log-level.md)
* [Allow failures](allow-failures.md)
* [Stop on failure](stop-on-failure.md)
