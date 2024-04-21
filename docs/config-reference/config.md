# Configuration file <Badge type="tip" text="3.0+" />

<small>🖥️&nbsp;Option: `-c`, `--config`</small>

> Path to an external configuration file.

::: tip SEE ALSO
Read more in the [Configuration](../configuration.md#configuration-file) section.
:::

## Relative path

Provided config files can be relative to the current working directory.

::: code-group

```bash [CLI]
./cache-warmup.phar -c "cache-warmup.json"
./cache-warmup.phar --config "cache-warmup.json"
```

```bash [.env]
CACHE_WARMUP_CONFIG="cache-warmup.json"
```

:::

## Absolute path

You can also provide the absolute path to a config file.

::: code-group

```bash [CLI]
./cache-warmup.phar -c "/path/to/cache-warmup.json"
./cache-warmup.phar --config "/path/to/cache-warmup.json"
```

```bash [.env]
CACHE_WARMUP_CONFIG="/path/to/cache-warmup.json"
```

:::
