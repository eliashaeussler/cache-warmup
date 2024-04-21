# Log file <Badge type="tip" text="2.4.0+" />

<small>📝&nbsp;Name: `logFile` &middot; 🖥️&nbsp;Option: `--log-file`</small>

> A file where to log crawling results.

::: info
Implicitly enables logging, if this configuration option is set.
:::

## Example

Provide the path to a local file where to log result messages. Make sure
to either provide an **absolute path** or a path **relative to the working
directory**.

::: code-group

```bash [CLI]
./cache-warmup.phar --log-file cache-warmup.log
```

```json [JSON]
{
    "logFile": "cache-warmup.log"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setLogFile('cache-warmup.log');

    return $config;
};
```

```yaml [YAML]
logFile: cache-warmup.log
```

```bash [.env]
CACHE_WARMUP_LOG_FILE=cache-warmup.log
```

:::
