# Request Factory <Badge type="tip" text="4.0+" />

HTTP requests for cache warmup and XML sitemap parsing are built using the
[`Http\Message\RequestFactory`](../../src/Http/Message/RequestFactory.php).

::: warning IMPORTANT
The factory is stateful – it stores request method and headers.
:::

## Method Reference

The following methods are available:

### `__construct`

Pass HTTP request method and request headers to the constructor. They will
be used for each generated HTTP request.

```php {7}
use EliasHaeussler\CacheWarmup;

$headers = [
    'X-Foo' => 'Bar',
];

$clientFactory = new CacheWarmup\Http\Message\RequestFactory('GET', $headers);
```

### `build`

Generate HTTP request for given URL. It is built using the configured HTTP
method and includes configured request headers, merged with default
`User-Agent` header.

```php {7}
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$url = new Psr7\Uri('https://www.example.com/');

$clientFactory = new CacheWarmup\Http\Message\RequestFactory('GET');
$request = $clientFactory->build($url);
```

### `buildIterable`

Generate HTTP requests for given URLs, same as with the [`build`](#build)
method.

```php {11}
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$urls = [
    new Psr7\Uri('https://www.example.com/'),
    new Psr7\Uri('https://www.example.com/de/'),
    new Psr7\Uri('https://www.example.com/fr/'),
];

$clientFactory = new CacheWarmup\Http\Message\RequestFactory('GET');
$requests = $clientFactory->buildIterable($urls);
```

## Example

```php

use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$clientFactory = new CacheWarmup\Http\Message\RequestFactory('GET', [
    'X-Foo' => 'Bar',
]);

// One GET request with X-Foo and User-Agent headers
$request = $clientFactory->build(
    new Psr7\Uri('https://www.example.com/'),
);

// Multiple GET requests with X-Foo and User-Agent headers
$requests = $clientFactory->buildIterable([
    new Psr7\Uri('https://www.example.com/'),
    new Psr7\Uri('https://www.example.com/de/'),
    new Psr7\Uri('https://www.example.com/fr/'),
]);
```
