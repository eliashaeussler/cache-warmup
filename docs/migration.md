# Migration

This page lists breaking changes between major versions which may require
manual actions during upgrade.

::: tip
You can find all pull requests with breaking changes on
[GitHub](https://github.com/eliashaeussler/cache-warmup/pulls?q=is%3Apr+is%3Amerged+label%3Abreaking).
:::

## 3.x → 4.x

### New XML parser component ([#422])

A new interface [`Xml\Parser`](../src/Xml/Parser.php) is introduced as base
component for parsing XML sitemaps. The default XML parser is renamed from
`Xml\Parser` to [`Xml\SitemapXmlParser`](../src/Xml/SitemapXmlParser.php) and
implements the new [`Xml\Parser`](../src/Xml/Parser.php) interface.

* Migrate all existing class name references from `Xml\Parser` to `Xml\SitemapXmlParser`.
* When using a custom client within `CacheWarmer`, create the parser on your
  own and include your client implementation, then pass it to `CacheWarmer`:

  ```php
  use EliasHaeussler\CacheWarmup;
  use GuzzleHttp\Client;

  $client = new Client($clientConfig);
  $parser = new CacheWarmup\Xml\SitemapXmlParser(client: $client);
  $cacheWarmer = new CacheWarmup\CacheWarmer(parser: $parser);
  ```

### Changed crawling response body handling ([#424])

Default crawlers using [`Crawler\ConcurrentCrawlerTrait`](../src/Crawler/ConcurrentCrawlerTrait.php)
no longer attach crawling response body to response objects. A new crawler
option [`write_response_body`](config-reference/crawler-options.md#write-response-body)
is introduced to control this behavior.

* Set [`write_response_body`](config-reference/crawler-options.md#write-response-body)
  to `true` if you rely on the crawling response body. Note that this
  may significantly increase memory consumption and CPU load.

### Removal of `client_config` crawler option ([#442])

The `client_config` crawler option, which was previously respected by both
default crawlers, is dropped. A new [`Http\Client\ClientFactory`](../src/Http/Client/ClientFactory.php)
acts as drop-in replacement for globally shared client configuration.

* Migrate `client_config` crawler option to the new
  [`clientOptions`](config-reference/client-options.md) configuration option.
  Note that this configuration option will be respected by the default parser
  as well.



[#422]: https://github.com/eliashaeussler/cache-warmup/pull/422
[#424]: https://github.com/eliashaeussler/cache-warmup/pull/424
[#442]: https://github.com/eliashaeussler/cache-warmup/pull/442
