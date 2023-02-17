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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * ConcurrentCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ConcurrentCrawlerTest extends Framework\TestCase
{
    use Tests\Unit\CacheWarmupResultProcessorTrait;
    use Tests\Unit\ClientMockTrait;

    private Crawler\ConcurrentCrawler $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->subject = new Crawler\ConcurrentCrawler(client: $this->client);
    }

    /**
     * @test
     */
    public function constructorInstantiatesClientWithGivenClientConfig(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $subject = new Crawler\ConcurrentCrawler(
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

    /**
     * @test
     */
    public function constructorIgnoresGivenClientConfigIfInstantiatedClientIsPassed(): void
    {
        $subject = new Crawler\ConcurrentCrawler(
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function crawlHandlesSuccessfulRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Successful));
        self::assertSame([], $result->getFailed());
    }

    /**
     * @test
     */
    public function crawlHandlesFailedRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response(404));

        $urls = [
            new Psr7\Uri('https://www.foo.baz'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
    }
}
