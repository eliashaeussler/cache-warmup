# Dependency injection <Badge type="tip" text="3.2+" />

A limited service container is built to instantiate crawlers and
parsers. This allows custom crawlers to define dependencies to a
limited set of services.

## Included services

The container includes the following runtime services:

| Service                                                                                                               | Description                                                                                                                      | Added&nbsp;in                     |
|-----------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------|-----------------------------------|
| [`OutputInterface`](https://github.com/symfony/console/blob/v5.4.35/Output/OutputInterface.php)                       | Current output, either extracted from console application or constructed as `ConsoleOutput`                                      | <Badge type="tip" text="3.2.0" /> |
| [`LoggerInterface`](https://github.com/php-fig/log/blob/2.0.0/src/LoggerInterface.php)                                | Current logger, only available if the [`logFile`](../config-reference/log-file.md) configuration option is passed                | <Badge type="tip" text="3.2.0" /> |
| [`EventDispatcherInterface`](https://github.com/php-fig/event-dispatcher/blob/1.0.0/src/EventDispatcherInterface.php) | Current event dispatcher instance, either extracted from console application or constructed as new instance                      | <Badge type="tip" text="3.2.0" /> |
| [`ClientFactory`](../../src/Http/Client/ClientFactory.php)                                                            | Factory to create Guzzle client with defaults from [`clientOptions`](../config-reference/client-options.md) configuration option | <Badge type="tip" text="4.0.0" /> |
| [`ClientInterface`](https://github.com/guzzle/guzzle/blob/7.8.2/src/ClientInterface.php)                              | Shared Guzzle client, constructed with [`clientOptions`](../config-reference/client-options.md) configuration option             | <Badge type="tip" text="4.0.0" /> |

## Supported factories

The service container is built when creating crawlers and parsers
through their respective factories, including:

* [`EliasHaeussler\CacheWarmup\Crawler\CrawlerFactory`](../../src/Crawler/CrawlerFactory.php)
* [`EliasHaeussler\CacheWarmup\Xml\ParserFactory`](../../src/Xml/ParserFactory.php)
