---
outline: [2,3]
---

# Stoppable Crawler

In terms of error handling it might be useful to define the
behavior in case a cache warmup failure occurs. You can implement
[`EliasHaeussler\CacheWarmup\Crawler\StoppableCrawler`](../../src/Crawler/StoppableCrawler.php)
to make this sort of error handling configurable:

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler // [!code --]
final class MyCustomCrawler implements CacheWarmup\Crawler\StoppableCrawler // [!code ++]
{
    // ...
}
```

## Method Reference

You must implement the following method to allow configuration of
the crawler in cases of cache warmup failures:

### `stopOnFailure`

Configures the crawler to either stop or continue on failures.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;

final class MyCustomCrawler implements CacheWarmup\Crawler\StoppableCrawler
{
    private bool $stopOnFailure = false; // [!code ++]

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        // ...
    }

    public function stopOnFailure(bool $stopOnFailure = true): void // [!code ++]
    { // [!code ++]
        $this->stopOnFailure = $stopOnFailure; // [!code ++]
    } // [!code ++]

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```

## Example

```php {8,25-27,33-36}
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;

final class MyCustomCrawler implements CacheWarmup\Crawler\StoppableCrawler
{
    private bool $stopOnFailure = false;

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        $result = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($urls as $url) {
            $successful = $this->warmUp($url);

            if ($successful) {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createSuccessful($url);
            } else {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createFailed($url);
            }

            $result->addResult($crawlingResult);

            if (!$successful && $this->stopOnFailure) {
                break;
            }
        }

        return $result;
    }

    public function stopOnFailure(bool $stopOnFailure = true): void
    {
        $this->stopOnFailure = $stopOnFailure;
    }

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```
