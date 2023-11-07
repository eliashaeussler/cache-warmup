<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Tests\Crawler;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;
use Psr\Log;

/**
 * ConcurrentCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\ConcurrentCrawler::class)]
final class ConcurrentCrawlerTest extends Framework\TestCase
{
    use Tests\CacheWarmupResultProcessorTrait;
    use Tests\ClientMockTrait;

    private Src\Crawler\ConcurrentCrawler $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->subject = new Src\Crawler\ConcurrentCrawler(
            ['concurrency' => 1],
            $this->client,
        );
    }

    #[Framework\Attributes\Test]
    public function constructorInstantiatesClientWithGivenClientConfig(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $subject = new Src\Crawler\ConcurrentCrawler(
            [
                'client_config' => [
                    'handler' => $this->mockHandler,
                ],
            ],
        );

        self::assertNull($this->mockHandler->getLastRequest());

        $subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertNotNull($this->mockHandler->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function constructorIgnoresGivenClientConfigIfInstantiatedClientIsPassed(): void
    {
        $subject = new Src\Crawler\ConcurrentCrawler(
            [
                'client_config' => [
                    'handler' => $this->mockHandler,
                ],
            ],
            new Client(),
        );

        self::assertNull($this->mockHandler->getLastRequest());

        $subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertNull($this->mockHandler->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestToAllGivenUrls(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
        );

        $urls = [
            new Psr7\Uri('https://www.example.org'),
            new Psr7\Uri('https://www.example.com'),
            new Psr7\Uri('https://www.example.net'),
            new Psr7\Uri('https://www.example.edu'),
            new Psr7\Uri('https://www.beispiel.de'),
        ];

        $result = $this->subject->crawl($urls);
        $processedUrls = $this->getProcessedUrlsFromCacheWarmupResult($result);

        self::assertSame([], array_diff($urls, $processedUrls));
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestsWithDefaultUserAgentHeader(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $this->subject->crawl($urls);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(Message\RequestInterface::class, $lastRequest);
        self::assertStringStartsWith('EliasHaeussler-CacheWarmup/', $lastRequest->getHeader('User-Agent')[0] ?? '');
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestsWithOverriddenUserAgentHeader(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $subject = new Src\Crawler\ConcurrentCrawler(
            [
                'request_headers' => [
                    'User-Agent' => 'foo',
                ],
            ],
            $this->client,
        );

        $subject->crawl($urls);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(Message\RequestInterface::class, $lastRequest);
        self::assertSame(['foo'], $lastRequest->getHeader('User-Agent'));
    }

    #[Framework\Attributes\Test]
    public function crawlHandlesSuccessfulRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Successful));
        self::assertSame([], $result->getFailed());
    }

    #[Framework\Attributes\Test]
    public function crawlHandlesFailedRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response(404));

        $urls = [
            new Psr7\Uri('https://www.foo.baz'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
    }

    #[Framework\Attributes\Test]
    public function crawlLogsCrawlingResults(): void
    {
        $logger = new Tests\Log\DummyLogger();

        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(404),
        );

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->setLogger($logger);
        $this->subject->setLogLevel(Log\LogLevel::INFO);
        $this->subject->crawl([$uri1, $uri2]);

        self::assertCount(1, $logger->log[Log\LogLevel::ERROR]);
        self::assertCount(1, $logger->log[Log\LogLevel::INFO]);
    }

    #[Framework\Attributes\Test]
    public function crawlStopsOnFailure(): void
    {
        $this->mockHandler->append(new Psr7\Response(404));

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->stopOnFailure();

        $result = $this->subject->crawl([$uri1, $uri2]);

        self::assertSame([$uri1], $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
        self::assertTrue($result->wasCancelled());
    }
}
