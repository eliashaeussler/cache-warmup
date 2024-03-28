# Exclude patterns <Badge type="tip" text="2.0+" />

<small>📝 Name: `exclude` &middot; 🖥️ Option: `-e`, `--exclude` &middot; 📚 Multiple values allowed</small>

> Patterns of URLs to be excluded from cache warmup.

## Regular expression

Provide a regular expression with delimiter `#`.

::: code-group

```bash [CLI]
./cache-warmup.phar -e "#(no_cache|no_warming)=1#"
./cache-warmup.phar --exclude "#(no_cache|no_warming)=1#"
```

```json [JSON]
{
    "exclude": [
        "#(no_cache|no_warming)=1#"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addExcludePattern(
        CacheWarmup\Config\Option\ExcludePattern::createFromRegularExpression('#(no_cache|no_warming)=1#'),
    );

    return $config;
};
```

```yaml [YAML]
exclude:
  - '#(no_cache|no_warming)=1#'
```

```bash [.env]
CACHE_WARMUP_EXCLUDE="#(no_cache|no_warming)=1#"
```

:::

## `fnmatch` pattern

Provide a pattern that is supported by [`fnmatch`](https://www.php.net/manual/en/function.fnmatch).

::: code-group

```bash [CLI]
./cache-warmup.phar -e "*no_cache=1*"
./cache-warmup.phar --exclude "*no_cache=1*"
```

```json [JSON]
{
    "exclude": [
        "*no_cache=1*"
    ]
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->addExcludePattern(
        CacheWarmup\Config\Option\ExcludePattern::createFromPattern('*no_cache=1*'),
    );

    return $config;
};
```

```yaml [YAML]
exclude:
  - '*no_cache=1*'
```

```bash [.env]
CACHE_WARMUP_EXCLUDE="*no_cache=1*"
```

:::
