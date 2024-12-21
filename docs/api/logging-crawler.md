---
outline: [2,3]
---

# Logging Crawler <Badge type="tip" text="2.4+" />

In some cases it might be helpful to log parts of the cache
warmup process. For this, your crawler should implement
[`Crawler\LoggingCrawler`](../../src/Crawler/LoggingCrawler.php):

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler // [!code --]
final class MyCustomCrawler implements CacheWarmup\Crawler\LoggingCrawler // [!code ++]
{
    // ...
}
```

## Method Reference

The interface describes two methods to configure logging.

### `setLogger`

This method allows to inject an instance of a PSR-3 compliant
logger.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Psr\Log; // [!code ++]

final class MyCustomCrawler implements CacheWarmup\Crawler\LoggingCrawler
{
    private ?Log\LoggerInterface $logger = null; // [!code ++]

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        // ...
    }

    public function setLogger(Log\LoggerInterface $logger): void // [!code ++]
    { // [!code ++]
        $this->logger = $logger; // [!code ++]
    } // [!code ++]

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```

### `setLogLevel`

The default log level should be `error` as described in the
[`logLevel`](../config-reference/log-level.md) configuration
option. However, with this method the log level can be changed
as desired.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Psr\Log;

final class MyCustomCrawler implements CacheWarmup\Crawler\LoggingCrawler
{
    private ?Log\LoggerInterface $logger = null;
    private string $logLevel = CacheWarmup\Log\LogLevel::ERROR; // [!code ++]

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        // ...
    }

    public function setLogger(Log\LoggerInterface $logger): void
    {
        // ...
    }

    public function setLogLevel(string $logLevel): void // [!code ++]
    { // [!code ++]
        $this->logLevel = $logLevel; // [!code ++]
    } // [!code ++]

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```

## Example

```php {9-10,29-32,34-37,48-51,53-56}
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Psr\Log;

final class MyCustomCrawler implements CacheWarmup\Crawler\LoggingCrawler
{
    private ?Log\LoggerInterface $logger = null;
    private string $logLevel = CacheWarmup\Log\LogLevel::ERROR;

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

    public function setLogger(Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    private function warmUp(Message\UriInterface $url): bool
    {
        $successful = /* ... */

        if (null === $this->logger) {
            return $successful;
        }

        if ($successful) {
            $this->logger->info(
                'Successfully warmed up cache of {url}.',
                ['url' => $url],
            );
        } else {
            $this->logger->error(
                'Failed to warm up cache of {url}.',
                ['url' => $url],
            );
        }

        return $successful;
    }
}
```
