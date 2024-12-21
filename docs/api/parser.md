---
outline: [2,3]
---

# Create a custom parser

Parsers are used to fetch, validate and parse XML sitemaps.
They transform a given XML sitemap to appropriate objects
use for subsequent cache warmup. Each parser must implement
[`Xml\Parser`](../../src/Xml/Parser.php):

```php
namespace Vendor\Xml;

use EliasHaeussler\CacheWarmup;

final class MyCustomParser implements CacheWarmup\Xml\Parser
{
    // ...
}
```

## Method Reference

The default parser describes the following method:

### `parse`

This method fetches and parses a given XML sitemap and returns
all parsed sitemaps and URLs.

```php
namespace Vendor\Xml;

use EliasHaeussler\CacheWarmup;

final class MyCustomParser implements CacheWarmup\Xml\Parser
{
    public function parse(CacheWarmup\Sitemap\Sitemap $sitemap): CacheWarmup\Result\ParserResult // [!code ++]
    { // [!code ++]
        $xml = $this->fetchSitemap($sitemap); // [!code ++]
        $sitemaps = $this->extractSitemapsFromXml($xml); // [!code ++]
        $urls = $this->extractUrlsFromXml($xml); // [!code ++]
​// [!code ++]
        return new CacheWarmup\Result\ParserResult($sitemaps, $urls); // [!code ++]
    } // [!code ++]
​// [!code ++]
    private function fetchSitemap(CacheWarmup\Sitemap\Sitemap $sitemap): string // [!code ++]
    { // [!code ++]
        // ... // [!code ++]
    } // [!code ++]
​// [!code ++]
    /** // [!code ++]
     * @return list<CacheWarmup\Sitemap\Sitemap> // [!code ++]
     */ // [!code ++]
    private function extractSitemapsFromXml(string $xml): array // [!code ++]
    { // [!code ++]
        // ... // [!code ++]
    } // [!code ++]
​// [!code ++]
    /** // [!code ++]
     * @return list<CacheWarmup\Sitemap\Url> // [!code ++]
     */ // [!code ++]
    private function extractUrlsFromXml(string $xml): array // [!code ++]
    { // [!code ++]
        // ... // [!code ++]
    } // [!code ++]
}
```
