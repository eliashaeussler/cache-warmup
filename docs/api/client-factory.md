# Client Factory <Badge type="tip" text="4.0+" />

Crawlers and parsers perform HTTP requests to perform cache warmup and
parse XML sitemaps. Both use the [`Http\Client\ClientFactory`](../../src/Http/Client/ClientFactory.php)
to create a consistent Guzzle client instance across all consumers.

## Method Reference

The following methods are available:

### `__construct`

Use the constructor to pass a set of default
[client configuration](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client).
It will be used for each generated client.

```php {8}
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\HandlerStack;

$defaults = [
    'handler' => HandlerStack::create(/* ... */),
];

$clientFactory = new CacheWarmup\Http\Client\ClientFactory($defaults);
```

### `get`

Create a new Guzzle client with a given set of additional
[client configuration](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client).
Additional configuration will be merged with default configuration from the
constructor.

```php {4}
use EliasHaeussler\CacheWarmup;

$clientFactory = new CacheWarmup\Http\Client\ClientFactory();
$client = $clientFactory->get();
```

## Example

```php
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;

$defaults = [
    'handler' => HandlerStack::create(/* ... */),
];
$clientFactory = new CacheWarmup\Http\Client\ClientFactory($defaults);

// Create default client with custom handler
$defaultClient = $clientFactory->get();

// Create client with custom handler and basic auth credentials
$authenticatedClient = $clientFactory->get([
    RequestOptions::AUTH => ['username', 'password'],
]);
```
