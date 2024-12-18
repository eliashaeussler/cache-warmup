# Parser <Badge type="tip" text="4.0+" />

<small>📝&nbsp;Name: `parser` &middot; 🖥️&nbsp;Option: `--parser`</small>

> FQCN of the parser to be used for parsing XML sitemaps.

::: tip
You can also [implement a custom parser](../api/parser.md) that fits your needs.
:::

## Example

Make sure the parser can be autoloaded by PHP and provide the FQCN.

::: code-group

```bash [CLI]
./cache-warmup.phar --parser "Vendor\\Xml\\MyCustomParser"
```

```json [JSON]
{
    "parser": "Vendor\\Xml\\MyCustomParser"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setParser(\Vendor\Xml\MyCustomParser::class);

    return $config;
};
```

```yaml [YAML]
parser: 'Vendor\\Xml\\MyCustomParser'
```

```bash [.env]
CACHE_WARMUP_PARSER="Vendor\\Xml\\MyCustomParser"
```

:::
