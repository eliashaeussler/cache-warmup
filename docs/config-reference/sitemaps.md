# Sitemaps <Badge type="tip" text="0.1.0+" />

<small>📝 Name: `sitemaps` &middot; 🚨 Required &middot; 📚 Multiple values allowed</small>

> URLs or local filenames of XML sitemaps to be warmed up.

## URL

Provide the URL to an XML sitemap. Make sure to include the URL
protocol, otherwise the URL cannot be resolved.

::: code-group

```bash [CLI]
./cache-warmup.phar "https://www.example.org/sitemap.xml"
```

```json [JSON]
{
    "sitemaps": [
        "https://www.example.org/sitemap.xml"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addSitemap(
        CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/sitemap.xml'),
    );

    return $config;
};
```

```yaml [YAML]
sitemaps:
  - https://www.example.org/sitemap.xml
```

```bash [.env]
CACHE_WARMUP_SITEMAPS="https://www.example.org/sitemap.xml"
```

:::

## Local file

Provide the path to a local file which contains an XML sitemap. Make sure
to either provide an **absolute path** or a path **relative to the working
directory**.

::: code-group

```bash [CLI]
./cache-warmup.phar "/var/www/html/sitemap.xml"
```

```json [JSON]
{
    "sitemaps": [
        "/var/www/html/sitemap.xml"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addSitemap(
        CacheWarmup\Sitemap\Sitemap::createFromString('/var/www/html/sitemap.xml'),
    );

    return $config;
};
```

```yaml [YAML]
sitemaps:
  - https://www.example.org/sitemap.xml
  - /var/www/html/sitemap.xml
```

```bash [.env]
CACHE_WARMUP_SITEMAPS="/var/www/html/sitemap.xml"
```

:::

## Multiple sitemaps

The library also supports parsing of multiple XML sitemaps. You may
then [limit](limit.md) the number of URLs to be warmed up to avoid
huge server load.

::: code-group

```bash [CLI]
./cache-warmup.phar "https://www.example.org/sitemap.xml" "/var/www/html/sitemap.xml"
```

```json [JSON]
{
    "sitemaps": [
        "https://www.example.org/sitemap.xml",
        "/var/www/html/sitemap.xml"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addSitemap(
        CacheWarmup\Sitemap\Sitemap::createFromString('https://www.example.org/sitemap.xml'),
    );
    $config->addSitemap(
        CacheWarmup\Sitemap\Sitemap::createFromString('/var/www/html/sitemap.xml'),
    );

    return $config;
};
```

```yaml [YAML]
sitemaps:
  - https://www.example.org/sitemap.xml
  - /var/www/html/sitemap.xml
```

```bash [.env]
CACHE_WARMUP_SITEMAPS="https://www.example.org/sitemap.xml, /var/www/html/sitemap.xml"
```

:::
