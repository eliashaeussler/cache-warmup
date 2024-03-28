# Stop on failure <Badge type="tip" text="2.7.0+" />

<small>📝 Name: `stopOnFailure` &middot; 🖥️ Option: `--stop-on-failure`</small>

> Cancel further cache warmup requests on failure.

::: info
This option only apply to crawlers implementing
[`EliasHaeussler\CacheWarmup\Crawler\StoppableCrawlerInterface`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/StoppableCrawlerInterface.php).
If the configured crawler does not implement this interface, a warning is
shown in case this flag is enabled.
:::

## Example

Enable the flag to immediately stop crawling on failure.

::: code-group

```bash [CLI]
./cache-warmup.phar --stop-on-failure
```

```json [JSON]
{
    "stopOnFailure": true
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->stopOnFailure();

    return $config;
};
```

```yaml [YAML]
stopOnFailure: true
```

```bash [.env]
CACHE_WARMUP_STOP_ON_FAILURE=1
```

:::
