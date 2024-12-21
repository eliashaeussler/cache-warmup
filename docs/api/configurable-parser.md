---
outline: [2,3]
---

# Configurable Parser <Badge type="tip" text="4.0+" />

Whenever you want to allow a custom behavior of your parser, it
should implement
[`EliasHaeussler\CacheWarmup\Xml\ConfigurableParser`](../../src/Xml/ConfigurableParser.php):

```php
namespace Vendor\Xml;

use EliasHaeussler\CacheWarmup;

final class MyCustomParser implements CacheWarmup\Xml\Parser // [!code --]
final class MyCustomParser implements CacheWarmup\Xml\ConfigurableParser // [!code ++]
{
    // ...
}
```

## Method Reference

The interface describes additional possibilities to pass
[parser options](../config-reference/parser-options.md) to your
crawler:

### `setOptions`

This method is called when custom parser options are given. They
should be persisted inside the parser to change its behavior as
desired.

```php
namespace Vendor\Xml;

use EliasHaeussler\CacheWarmup;

final class MyCustomParser implements CacheWarmup\Xml\ConfigurableParser
{
    private array $options = [ // [!code ++]
        'request_options' => [], // [!code ++]
    ]; // [!code ++]
​// [!code ++]
    public function __construct(array $options = []) // [!code ++]
    { // [!code ++]
        $this->setOptions($options); // [!code ++]
    } // [!code ++]

    public function parse(CacheWarmup\Sitemap\Sitemap $sitemap): CacheWarmup\Result\ParserResult
    {
        $xml = $this->fetchSitemap($sitemap);
        $sitemaps = $this->extractSitemapsFromXml($xml);
        $urls = $this->extractUrlsFromXml($xml);

        return new CacheWarmup\Result\ParserResult($sitemaps, $urls);
    }

    public function setOptions(array $options): void // [!code ++]
    { // [!code ++]
        $this->options = [...$this->options, ...$options]; // [!code ++]
    } // [!code ++]

    private function fetchSitemap(CacheWarmup\Sitemap\Sitemap $sitemap): string
    {
        // ...
    }

    /**
     * @return list<CacheWarmup\Sitemap\Sitemap>
     */
    private function extractSitemapsFromXml(string $xml): array
    {
        // ...
    }

    /**
     * @return list<CacheWarmup\Sitemap\Url>
     */
    private function extractUrlsFromXml(string $xml): array
    {
        // ...
    }
}
```

## Example

```php {9-16,27-30,38}
namespace Vendor\Xml;

use EliasHaeussler\CacheWarmup;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message;

final class MyCustomParser implements CacheWarmup\Xml\ConfigurableParser
{
    private array $options = [
        'request_options' => [],
    ];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function parse(CacheWarmup\Sitemap\Sitemap $sitemap): CacheWarmup\Result\ParserResult
    {
        $xml = $this->fetchSitemap($sitemap);
        $sitemaps = $this->extractSitemapsFromXml($xml);
        $urls = $this->extractUrlsFromXml($xml);

        return new CacheWarmup\Result\ParserResult($sitemaps, $urls);
    }

    public function setOptions(array $options): void
    {
        $this->options = [...$this->options, ...$options];
    }

    private function fetchSitemap(CacheWarmup\Sitemap\Sitemap $sitemap): string
    {
        $client = $this->createClient();
        $response = $client->request(
            'GET',
            $sitemap->getUri(),
            $this->options['request_options'],
        );

        return (string) $response->getBody();
    }

    private function createClient(): ClientInterface
    {
        // ...
    }

    /**
     * @return list<CacheWarmup\Sitemap\Sitemap>
     */
    private function extractSitemapsFromXml(string $xml): array
    {
        // ...
    }

    /**
     * @return list<CacheWarmup\Sitemap\Url>
     */
    private function extractUrlsFromXml(string $xml): array
    {
        // ...
    }
}
```
