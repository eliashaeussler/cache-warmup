# Events <Badge type="tip" text="3.2+" />

Most parts during cache warmup allow customization through
the use of events. An event dispatcher instance is passed
around which allows custom listeners to react on several
actions. This page lists all currently available events.

## Configuration

### [`ConfigResolved`](../../src/Event/ConfigResolved.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; 🚫&nbsp;PHP API</small>

> Dispatched after final config is resolved.

## Parser

### [`SitemapAdded`](../../src/Event/SitemapAdded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an XML sitemap is added for cache warmup.

### [`SitemapExcluded`](../../src/Event/SitemapExcluded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if an XML sitemap was skipped due to a
> configured [exclude pattern](../config-reference/exclude.md).

### [`SitemapParsed`](../../src/Event/SitemapParsed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched once an XML sitemap is successfully parsed.

### [`SitemapParsingFailed`](../../src/Event/SitemapParsingFailed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if parsing of an XML sitemap failed due to an error.

### [`UrlAdded`](../../src/Event/UrlAdded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if a URL is added for cache warmup.

### [`UrlExcluded`](../../src/Event/UrlExcluded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if a URL was skipped due to a configured
> [exclude pattern](../config-reference/exclude.md).

## Crawler

### [`UrlsPrepared`](../../src/Event/UrlsPrepared.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched if URLs are prepared due to a configured
> [crawling strategy](../config-reference/strategy.md).

### [`CrawlingStarted`](../../src/Event/CrawlingStarted.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched right before crawling is started.

### [`UrlCrawlingSucceeded`](../../src/Event/UrlCrawlingSucceeded.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched when crawling of a single URL was successful.

::: warning IMPORTANT
This event is only dispatched if the configured crawler utilizes the
[`ResultCollectorHandler`](response-handlers.md#resultcollectorhandler).
:::

### [`UrlCrawlingFailed`](../../src/Event/UrlCrawlingFailed.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched when crawling of a single URL failed.

::: warning IMPORTANT
This event is only dispatched if the configured crawler utilizes the
[`ResultCollectorHandler`](response-handlers.md#resultcollectorhandler).
:::

### [`CrawlingFinished`](../../src/Event/CrawlingFinished.php) <Badge type="tip" text="3.2+" />

<small>✅&nbsp;Console command &middot; ✅&nbsp;PHP API</small>

> Dispatched once crawling is finished.
