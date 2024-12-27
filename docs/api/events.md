# Events <Badge type="tip" text="3.2+" />

Most parts during cache warmup allow customization through
the use of events. An event dispatcher instance is passed
around which allows custom listeners to react on several
actions. This page lists all currently available events.

## Available events

* Configuration
  - [`ConfigResolved`](#configresolved)
* Parser
  - [`ParserConstructed`](#parserconstructed)
  - [`SitemapAdded`](#sitemapadded)
  - [`SitemapExcluded`](#sitemapexcluded)
  - [`SitemapParsed`](#sitemapparsed)
  - [`SitemapParsingFailed`](#sitemapparsingfailed)
  - [`UrlAdded`](#urladded)
  - [`UrlExcluded`](#urlexcluded)
* Crawler
  - [`CrawlerConstructed`](#crawlerconstructed)
  - [`UrlsPrepared`](#urlsprepared)
  - [`CrawlingStarted`](#crawlingstarted)
  - [`UrlCrawlingSucceeded`](#urlcrawlingsucceeded)
  - [`UrlCrawlingFailed`](#urlcrawlingfailed)
  - [`CrawlingFinished`](#crawlingfinished)
* HTTP
  - [`ClientConstructed`](#clientconstructed)

## Configuration

### [`ConfigResolved`](../../src/Event/Config/ConfigResolved.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; 🚫&nbsp;PHP API</small>

> Dispatched after final config is resolved.

## Parser

### [`ParserConstructed`](../../src/Event/Parser/ParserConstructed.php) <Badge type="tip" text="4.0+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an XML parser is constructed by the parser factory.

### [`SitemapAdded`](../../src/Event/Parser/SitemapAdded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an XML sitemap is added for cache warmup.

### [`SitemapExcluded`](../../src/Event/Parser/SitemapExcluded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an XML sitemap was skipped due to a
> configured [exclude pattern](../config-reference/exclude.md).

### [`SitemapParsed`](../../src/Event/Parser/SitemapParsed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched once an XML sitemap is successfully parsed.

### [`SitemapParsingFailed`](../../src/Event/Parser/SitemapParsingFailed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if parsing of an XML sitemap failed due to an error.

### [`UrlAdded`](../../src/Event/Parser/UrlAdded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if a URL is added for cache warmup.

### [`UrlExcluded`](../../src/Event/Parser/UrlExcluded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if a URL was skipped due to a configured
> [exclude pattern](../config-reference/exclude.md).

## Crawler

### [`CrawlerConstructed`](../../src/Event/Crawler/CrawlerConstructed.php) <Badge type="tip" text="4.0+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if a crawler is constructed by the crawler factory.

### [`UrlsPrepared`](../../src/Event/Crawler/UrlsPrepared.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if URLs are prepared due to a configured
> [crawling strategy](../config-reference/strategy.md).

### [`CrawlingStarted`](../../src/Event/Crawler/CrawlingStarted.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched right before crawling is started.

### [`UrlCrawlingSucceeded`](../../src/Event/Crawler/UrlCrawlingSucceeded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched when crawling of a single URL was successful.

::: warning IMPORTANT
This event is only dispatched if the configured crawler utilizes the
[`ResultCollectorHandler`](response-handlers.md#resultcollectorhandler).
:::

### [`UrlCrawlingFailed`](../../src/Event/Crawler/UrlCrawlingFailed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched when crawling of a single URL failed.

::: warning IMPORTANT
This event is only dispatched if the configured crawler utilizes the
[`ResultCollectorHandler`](response-handlers.md#resultcollectorhandler).
:::

### [`CrawlingFinished`](../../src/Event/Crawler/CrawlingFinished.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched once crawling is finished.

## HTTP

### [`ClientConstructed`](../../src/Event/Http/ClientConstructed.php) <Badge type="tip" text="4.0+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an HTTP client is constructed by the client factory.
