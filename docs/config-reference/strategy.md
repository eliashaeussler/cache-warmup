# Crawling strategy <Badge type="tip" text="2.0+" />

<small>📝&nbsp;Name: `strategy` &middot; 🖥️&nbsp;Option: `-s`, `--strategy`</small>

> Optional crawling strategy to prepare URLs before crawling them.

## Sort by `changefreq`

Sorts collected URLs by their `changefreq` value in an XML sitemap before
crawling them.

::: code-group

```bash [CLI]
./cache-warmup.phar -s sort-by-changefreq
./cache-warmup.phar --strategy sort-by-changefreq
```

```json [JSON]
{
    "strategy": "sort-by-changefreq"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setStrategy(
        new CacheWarmup\Crawler\Strategy\SortByChangeFrequencyStrategy(),
    );

    return $config;
};
```

```yaml [YAML]
strategy: sort-by-changefreq
```

```bash [.env]
CACHE_WARMUP_STRATEGY=sort-by-changefreq
```

:::

## Sort by `lastmod`

Sorts collected URLs by their `lastmod` value in an XML sitemap before
crawling them.

::: code-group

```bash [CLI]
./cache-warmup.phar -s sort-by-lastmod
./cache-warmup.phar --strategy sort-by-lastmod
```

```json [JSON]
{
    "strategy": "sort-by-lastmod"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setStrategy(
        new CacheWarmup\Crawler\Strategy\SortByLastModificationDateStrategy(),
    );

    return $config;
};
```

```yaml [YAML]
strategy: sort-by-lastmod
```

```bash [.env]
CACHE_WARMUP_STRATEGY=sort-by-lastmod
```

:::

## Sort by `priority`

Sorts collected URLs by their `priority` value in an XML sitemap before
crawling them.

::: code-group

```bash [CLI]
./cache-warmup.phar -s sort-by-priority
./cache-warmup.phar --strategy sort-by-priority
```

```json [JSON]
{
    "strategy": "sort-by-priority"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setStrategy(
        new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
    );

    return $config;
};
```

```yaml [YAML]
strategy: sort-by-priority
```

```bash [.env]
CACHE_WARMUP_STRATEGY=sort-by-priority
```

:::
