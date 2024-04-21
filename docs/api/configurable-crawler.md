---
outline: [2,3]
---

# Configurable Crawler

Whenever you want to allow a custom behavior of your crawler, it
should implement
[`EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawler`](../../src/Crawler/ConfigurableCrawler.php):

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler // [!code --]
final class MyCustomCrawler implements CacheWarmup\Crawler\ConfigurableCrawler // [!code ++]
{
    // ...
}
```

## Method Reference

The interface describes additional possibilities to pass
[crawler options](../config-reference/crawler-options.md) to your
crawler:

### `setOptions`

This method is called when custom crawler options are given. They
should be persisted inside the crawler to change its behavior as
desired.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;

final class MyCustomCrawler implements CacheWarmup\Crawler\ConfigurableCrawler
{
    private array $options = [ // [!code ++]
        'request_method' => 'GET', // [!code ++]
    ]; // [!code ++]
​// [!code ++]
    public function __construct(array $options = []) // [!code ++]
    { // [!code ++]
        $this->setOptions($options); // [!code ++]
    } // [!code ++]

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        // ...
    }

    public function setOptions(array $options): void // [!code ++]
    { // [!code ++]
        $this->options = [...$this->options, ...$options]; // [!code ++]
    } // [!code ++]

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```

## Example

```php {9-16,35-38,43}
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message;

final class MyCustomCrawler implements CacheWarmup\Crawler\ConfigurableCrawler
{
    private array $options = [
        'request_method' => 'GET',
    ];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        $result = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($urls as $url) {
            if ($this->warmUp($url)) {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createSuccessful($url);
            } else {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createFailed($url);
            }

            $result->addResult($crawlingResult);
        }

        return $result;
    }

    public function setOptions(array $options): void
    {
        $this->options = [...$this->options, ...$options];
    }

    private function warmUp(Message\UriInterface $url): bool
    {
        $client = $this->createClient();
        $response = $client->request($this->options['request_method'], $url);

        return $response < 400;
    }

    private function createClient(): ClientInterface
    {
        // ...
    }
}
```
