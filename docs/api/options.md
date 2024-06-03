# Option Reference

The `CacheWarmer` class accepts a limited set of configuration
options. They can be provided as constructor parameters.

## `limit`

<small>🐝&nbsp;Default: `0`</small>

> *Same as the [`limit`](../config-reference/limit.md) configuration option.*

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    limit: 10,
);
$cacheWarmer->run();
```

## `client`

<small>🐝&nbsp;Default: `new GuzzleHttp\Client()`</small>

> A preconfigured Guzzle client to use when parsing XML sitemaps.

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    client: new \GuzzleHttp\Client([
        'handler' => $handler,
    ]),
);
$cacheWarmer->run();
```

## `crawler`

<small>🐝&nbsp;Default: `new EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler()`</small>

> *Same as the [`crawler`](../config-reference/crawler.md) configuration option.*

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    crawler: new \Vendor\Crawler\MyCustomCrawler(),
);
$cacheWarmer->run();
```

## `strategy`

<small>🐝&nbsp;Default: `null`</small>

> *Same as the [`strategy`](../config-reference/strategy.md) configuration option.*

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    strategy: new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
);
$cacheWarmer->run();
```

## `strict`

<small>🐝&nbsp;Default: `true`</small>

> *Opposite of the [`allowFailures`](../config-reference/allow-failures.md) configuration option.*

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    strict: false,
);
$cacheWarmer->run();
```

## `excludePatterns`

<small>🐝&nbsp;Default: `[]`</small>

> *Same as the [`exclude`](../config-reference/exclude.md) configuration option.*

```php
use EliasHaeussler\CacheWarmup;

$cacheWarmer = new CacheWarmup\CacheWarmer(
    excludePatterns: [
        CacheWarmup\Config\Option\ExcludePattern::create('*foo*'),
    ],
);
$cacheWarmer->run();
```
