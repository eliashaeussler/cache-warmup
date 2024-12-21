---
outline: [2,3]
---

# Client options <Badge type="tip" text="4.0+" />

<small>📝&nbsp;Name: `clientOptions` &middot; 🖥️&nbsp;Option:  `-t`, `--client-options`</small>

> Additional [configuration](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client)
> for shared Guzzle client.

::: info
The shared Guzzle client is used in default crawlers and default parser. Custom
crawlers and parsers may also use it, if properly configured via
[dependency injection](../api/dependency-injection.md).
:::

## Example

Pass client options in the expected input format.

::: warning IMPORTANT
When passing client options as **command parameter** or **environment variable**,
make sure to pass them as **JSON-encoded string**.
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --client-options '{"auth": ["username", "password"]}'
```

```json [JSON]
{
    "clientOptions": {
        "auth": ["username", "password"]
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setClientOption('auth', ['username', 'password']);

    return $config;
};
```

```yaml [YAML]
clientOptions:
  auth: ['username', 'password']
```

```bash [.env]
CACHE_WARMUP_CLIENT_OPTIONS='{"auth": ["username", "password"]}'
```

:::
