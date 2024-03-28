# Method Reference

The `CacheWarmer` provides various methods to prepare and inspect
processed URLs.

## `run`

Uses the configured [crawler](options.md#crawler) to crawl all
configured and collected URLs. In case a [strategy](options.md#strategy)
is configured, the URLs are prepared first.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$result = $cacheWarmer->run();
```

Once cache warmup is finished, this method returns an instance of
[`EliasHaeussler\CacheWarmup\Result\CacheWarmupResult`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Result/CacheWarmupResult.php)
with all successful and failed crawling results:

```php
foreach $result->getSuccessful() as $successfulResult) {
    echo 'Success: '.$successfulResult.PHP_EOL;
}
foreach $result->getFailed() as $failedResult) {
    echo 'Failed: '.$failedResult.PHP_EOL;
}
```

## `addSitemaps`

Parses a given XML sitemap or list of XML sitemaps and configures
the resulting URLs to be crawled when running cache warmup. In case
a [limit](options.md#limit) is configured, the given XML sitemaps
may be skipped if the limit is already reached.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();

// Sitemap as string
$cacheWarmer->addSitemaps('https://www.example.org/sitemap.xml');

// Sitemap as object
$cacheWarmer->addSitemaps(
    CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/sitemap.xml'),
);

// List of sitemaps
$cacheWarmer->addSitemaps([
    CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/sitemap.xml'),
    CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/de/sitemap.xml'),
]);
```

## `addUrl`

Configures the given URL to be crawled when running cache warmup.
In case a [limit](options.md#limit) is configured, it may be skipped
if the limit is already reached. If the given URL matches a
configured [exclude pattern](options.md#excludepatterns), it may
be skipped as well.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();

// URL as string
$cacheWarmer->addUrl('https://www.example.org/');

// URL as object
$cacheWarmer->addUrl(
    new CacheWarmup\Sitemap\Url('https://www.example.org/'),
);
```

## `getUrls`

Get list of all URLs to be warmed up with the next run.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$urls = $cacheWarmer->getUrls();

echo 'The following URLs will be crawled:'.PHP_EOL;

foreach ($urls as $url) {
    echo ' * '.$url.PHP_EOL;
}
```

## `getSitemaps`

Get list of all parsed XML sitemaps.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$sitemaps = $cacheWarmer->getSitemaps();

echo 'The following XML sitemaps were parsed:'.PHP_EOL;

foreach ($sitemaps as $sitemap) {
    echo ' * '.$sitemap.PHP_EOL;
}
```

## `getFailedSitemaps`

Get list of all XML sitemaps that could not be parsed due to
an error. In case [strict](options.md#strict) mode is enabled,
it will always be empty, because parse errors are immediately
thrown.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$sitemaps = $cacheWarmer->getFailedSitemaps();

echo 'The following XML sitemaps could not be parsed:'.PHP_EOL;

foreach ($sitemaps as $sitemap) {
    echo ' * '.$sitemap.PHP_EOL;
}
```

## `getExcludedSitemaps`

Get list of all XML sitemaps that were skipped because of a
configured [exclude pattern](options.md#excludepatterns).

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$sitemaps = $cacheWarmer->getExcludedSitemaps();

echo 'The following XML sitemaps were skipped:'.PHP_EOL;

foreach ($sitemaps as $sitemap) {
    echo ' * '.$sitemap.PHP_EOL;
}
```

## `getExcludedUrls`

Get list of all URLs that were skipped because of a configured
[exclude pattern](options.md#excludepatterns).

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer();
$urls = $cacheWarmer->getExcludedUrls();

echo 'The following URLs were skipped:'.PHP_EOL;

foreach ($urls as $url) {
    echo ' * '.$url.PHP_EOL;
}
```
