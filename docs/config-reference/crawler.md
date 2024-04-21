# Crawler <Badge type="tip" text="0.1.0+" />

<small>📝&nbsp;Name: `crawler` &middot; 🖥️&nbsp;Option: `-c`, `--crawler`</small>

> FQCN of the crawler to use for cache warmup.

::: info
The default crawler depends on whether the configuration option
[`progress`](progress.md) is set. In this case the
[`OutputtingCrawler`](../../src/Crawler/OutputtingCrawler.php)
is used, otherwise the
[`ConcurrentCrawler`](../../src/Crawler/ConcurrentCrawler.php).
:::

::: tip
You can also [implement a custom crawler](../api/crawler.md) that fits your needs.
:::

## Example

Make sure the crawler can be autoloaded by PHP and provide the FQCN.

::: code-group

```bash [CLI]
./cache-warmup.phar -c "Vendor\\Crawler\\MyCustomCrawler"
./cache-warmup.phar --crawler "Vendor\\Crawler\\MyCustomCrawler"
```

```json [JSON]
{
    "crawler": "Vendor\\Crawler\\MyCustomCrawler"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawler(\Vendor\Crawler\MyCustomCrawler::class);

    return $config;
};
```

```yaml [YAML]
crawler: 'Vendor\\Crawler\\MyCustomCrawler'
```

```bash [.env]
CACHE_WARMUP_LIMIT="Vendor\\Crawler\\MyCustomCrawler"
```

:::
