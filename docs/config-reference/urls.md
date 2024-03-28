# URLs <Badge type="tip" text="0.1.0+" />

<small>📝 Name: `urls` &middot; 🖥️ Option: `-u`, `--urls` &middot; 📚 Multiple values allowed</small>

> Additional URLs to be warmed up.

## Example

Provide an additional URL and make sure to include the URL
protocol, otherwise the URL cannot be resolved.

::: code-group

```bash [CLI]
./cache-warmup.phar -u "https://www.example.org/" -u "https://www.example.org/de/"
./cache-warmup.phar --urls "https://www.example.org/" --urls "https://www.example.org/de/"
```

```json [JSON]
{
    "urls": [
        "https://www.example.org/",
        "https://www.example.org/de/"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addUrl(
        new CacheWarmup\Sitemap\Url('https://www.example.org/'),
    );
    $config->addUrl(
        new CacheWarmup\Sitemap\Url('https://www.example.org/de/'),
    );

    return $config;
};
```

```yaml [YAML]
urls:
  - https://www.example.org/
  - https://www.example.org/de/
```

```bash [.env]
CACHE_WARMUP_URLS="https://www.example.org/, https://www.example.org/de/"
```

:::
