<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler;
use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\CacheWarmup\Tests\Unit\CrawlerResultProcessorTrait;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * ConcurrentCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ConcurrentCrawlerTest extends TestCase
{
    use CrawlerResultProcessorTrait;

    /**
     * @test
     */
    public function crawlSendsRequestToAllGivenUrls(): void
    {
        $urls = [
            new Uri('https://www.example.org'),
            new Uri('https://www.example.com'),
            new Uri('https://www.example.net'),
            new Uri('https://www.example.edu'),
            new Uri('https://www.beispiel.de'),
        ];
        $subject = new ConcurrentCrawler();
        $subject->crawl($urls);

        $processedUrls = $this->getProcessedUrlsFromCrawler($subject);
        self::assertTrue([] === array_diff($urls, $processedUrls));
    }

    /**
     * @test
     */
    public function crawlHandlesSuccessfulRequestsOfAllGivenUrls(): void
    {
        $urls = [
            new Uri('https://www.example.org'),
        ];
        $subject = new ConcurrentCrawler();
        $subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCrawler($subject, CrawlingState::SUCCESSFUL));
        self::assertSame([], $subject->getFailedUrls());
    }

    /**
     * @test
     */
    public function crawlHandlesFailedRequestsOfAllGivenUrls(): void
    {
        $urls = [
            new Uri('https://www.foo.baz'),
        ];
        $subject = new ConcurrentCrawler();
        $subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCrawler($subject, CrawlingState::FAILED));
        self::assertSame([], $subject->getSuccessfulUrls());
    }
}
