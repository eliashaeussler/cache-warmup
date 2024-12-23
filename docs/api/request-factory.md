# Request Factory <Badge type="tip" text="4.0+" />

HTTP requests for cache warmup and XML sitemap parsing are built using the
[`Http\Message\RequestFactory`](../../src/Http/Message/RequestFactory.php).

::: warning IMPORTANT
The factory is stateful – it stores request method and headers.
:::

## Method Reference

The following methods are available:

### `create`

Pass HTTP request method which will be used for each generated HTTP request.

```php {3}
use EliasHaeussler\CacheWarmup;

$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET');
```

### `createRequest`

Generate HTTP request for given URL. It is built using the configured HTTP
method and includes configured request headers.

```php {7}
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$url = new Psr7\Uri('https://www.example.com/');

$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET');
$request = $clientFactory->createRequest($url);
```

### `createRequests`

Generate HTTP requests for given URLs, same as with the [`createRequest`](#createrequest)
method.

```php {11}
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$urls = [
    new Psr7\Uri('https://www.example.com/'),
    new Psr7\Uri('https://www.example.com/de/'),
    new Psr7\Uri('https://www.example.com/fr/'),
];

$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET');
$requests = $clientFactory->createRequests($urls);
```

### `withHeaders`

Pass request headers to include in each generated HTTP request.

```php {4-6}
use EliasHaeussler\CacheWarmup;

$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET')
    ->withHeaders([
        'X-Foo' => 'Bar',
    ])
;
```

### `withUserAgent`

Add default `User-Agent` header to request headers. The `User-Agent` header
is generated as follows (`<version>` is replaced by the current version of
the library):

```
EliasHaeussler-CacheWarmup/<version> (https://cache-warmup.dev)
```

::: info
If a `User-Agent` header is already configured using the `withHeaders()`
method, it will be overridden when calling the `withUserAgent()` method,
unless the `$skipIfAlreadyPresent` parameter is set to `true`.
:::

```php {5,11}
use EliasHaeussler\CacheWarmup;

// Enforce default User-Agent header
$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET')
    ->withUserAgent()
;

// Only add default User-Agent header if not already present
$clientFactory = CacheWarmup\Http\Message\RequestFactory::create('GET')
    ->withHeaders(['User-Agent' => 'Foo'])
    ->withUserAgent(true)
;
```

## Example

```php

use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Psr7;

$requestFactory = CacheWarmup\Http\Message\RequestFactory::create('GET');
$requestFactory = $requestFactory->withHeaders(['X-Foo' => 'Bar']);
$requestFactoryWithUserAgent = $requestFactory->withUserAgent();

// One GET request with X-Foo header
$request = $requestFactory->createRequest(
    new Psr7\Uri('https://www.example.com/'),
);

// Multiple GET requests with X-Foo headers
$requests = $requestFactory->createRequests([
    new Psr7\Uri('https://www.example.com/'),
    new Psr7\Uri('https://www.example.com/de/'),
    new Psr7\Uri('https://www.example.com/fr/'),
]);

// One GET request with X-Foo and User-Agent headers
$userAgentRequest = $requestFactoryWithUserAgent->createRequest(
    new Psr7\Uri('https://www.example.com/'),
);

// Multiple GET requests with X-Foo and User-Agent headers
$userAgentRequests = $requestFactoryWithUserAgent->createRequests([
    new Psr7\Uri('https://www.example.com/'),
    new Psr7\Uri('https://www.example.com/de/'),
    new Psr7\Uri('https://www.example.com/fr/'),
]);
```
