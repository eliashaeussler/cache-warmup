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
use GuzzleHttp\Handler;
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

    private bool $handlerPassed;
    private Crawler\ConcurrentCrawler $subject;

    protected function setUp(): void
    {
        $this->handlerPassed = false;
        $this->subject = new Crawler\ConcurrentCrawler([
            'client_config' => [
                'handler' => $this->createMockHandler(),
            ],
        ]);
    }

    /**
     * @test
     */
    public function constructorInstantiatesClientWithGivenClientConfig(): void
    {
        self::assertFalse($this->handlerPassed);

        $this->subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertTrue($this->handlerPassed);
    }

    /**
     * @test
     */
    public function constructorIgnoresGivenClientConfigIfInstantiatedClientIsPassed(): void
    {
        $this->subject = new Crawler\ConcurrentCrawler(
            [
                'client_config' => [
                    'handler' => $this->createMockHandler(),
                ],
            ],
            new Client(),
        );

        self::assertFalse($this->handlerPassed);

        $this->subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertFalse($this->handlerPassed);
    }

    /**
     * @test
     */
    public function crawlSendsRequestToAllGivenUrls(): void
    {
        $subject = new Crawler\ConcurrentCrawler();
        $urls = [
            new Psr7\Uri('https://www.example.org'),
            new Psr7\Uri('https://www.example.com'),
            new Psr7\Uri('https://www.example.net'),
            new Psr7\Uri('https://www.example.edu'),
            new Psr7\Uri('https://www.beispiel.de'),
        ];

        $result = $subject->crawl($urls);
        $processedUrls = $this->getProcessedUrlsFromCacheWarmupResult($result);

        self::assertSame([], array_diff($urls, $processedUrls));
    }

    /**
     * @test
     */
    public function crawlHandlesSuccessfulRequestsOfAllGivenUrls(): void
    {
        $subject = new Crawler\ConcurrentCrawler();
        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $result = $subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Successful));
        self::assertSame([], $result->getFailed());
    }

    /**
     * @test
     */
    public function crawlHandlesFailedRequestsOfAllGivenUrls(): void
    {
        $subject = new Crawler\ConcurrentCrawler();
        $urls = [
            new Psr7\Uri('https://www.foo.baz'),
        ];

        $result = $subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
    }

    private function createMockHandler(): Handler\MockHandler
    {
        return new Handler\MockHandler(
            [new Psr7\Response()],
            fn () => $this->handlerPassed = true,
        );
    }
}
