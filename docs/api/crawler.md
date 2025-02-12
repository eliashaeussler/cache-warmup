---
outline: [2,3]
---

# Create a custom crawler

Crawlers are the core component of the library. They are used
to perform the actual requests for all configured URLs to warm
up their website caches. Each crawler must implement [`Crawler\Crawler`](../../src/Crawler/Crawler.php):

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler
{
    // ...
}
```

## Method Reference

The default crawler describes the following method:

### `crawl`

When this method is called, the given list of URLs should be
warmed up.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message; // [!code ++]

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler
{
    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult // [!code ++]
    { // [!code ++]
        $result = new CacheWarmup\Result\CacheWarmupResult(); // [!code ++]
​// [!code ++]
        foreach ($urls as $url) { // [!code ++]
            if ($this->warmUp($url)) { // [!code ++]
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createSuccessful($url); // [!code ++]
            } else { // [!code ++]
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createFailed($url); // [!code ++]
            } // [!code ++]
​// [!code ++]
            $result->addResult($crawlingResult); // [!code ++]
        } // [!code ++]
​// [!code ++]
        return $result; // [!code ++]
    } // [!code ++]
​// [!code ++]
    private function warmUp(Message\UriInterface $url): bool // [!code ++]
    { // [!code ++]
        // ... // [!code ++]
    } // [!code ++]
}
```
