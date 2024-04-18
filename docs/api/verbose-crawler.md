---
outline: [2,3]
---

# Verbose Crawler

When cache warmup is performed from the command line, it might
be helpful to provide user-oriented output such as progress bars
or error messages. In this case, you can implement
[`EliasHaeussler\CacheWarmup\Crawler\VerboseCrawler`](../../src/Crawler/VerboseCrawler.php):

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;

final class MyCustomCrawler implements CacheWarmup\Crawler\Crawler // [!code --]
final class MyCustomCrawler implements CacheWarmup\Crawler\VerboseCrawler // [!code ++]
{
    // ...
}
```

## Method Reference

The interface describes only one method:

### `setOutput`

With this method, the current Symfony Output is injected.

```php
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Symfony\Component\Console;

final class MyCustomCrawler implements CacheWarmup\Crawler\VerboseCrawler
{
    private ?Console\Output\OutputInterface $output = null; // [!code ++]

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        // ...
    }

    public function setOutput(Console\Output\OutputInterface $output): void // [!code ++]
    { // [!code ++]
        $this->output = $output; // [!code ++]
    } // [!code ++]

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```

## Example

```php {9,14-15,17,30-33}
namespace Vendor\Crawler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Symfony\Component\Console;

final class MyCustomCrawler implements CacheWarmup\Crawler\VerboseCrawler
{
    private ?Console\Output\OutputInterface $output = null;

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        $result = new CacheWarmup\Result\CacheWarmupResult();
        $output = $this->output ?? new Console\Output\NullOutput();
        $progressBar = new Console\Helper\ProgressBar($output);

        foreach ($progressBar->iterate($urls) as $url) {
            if ($this->warmUp($url)) {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createSuccessful($url);
            } else {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createFailed($url);
            }

            $result->addResult($crawlingResult);
        }

        return $result;
    }

    public function setOutput(Console\Output\OutputInterface $output): void
    {
        $this->output = $output;
    }

    private function warmUp(Message\UriInterface $url): bool
    {
        // ...
    }
}
```
