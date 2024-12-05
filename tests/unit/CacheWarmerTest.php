<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

use function sprintf;

/**
 * CacheWarmerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\CacheWarmer::class)]
final class CacheWarmerTest extends Framework\TestCase
{
    use CacheWarmupResultProcessorTrait;
    use ClientMockTrait;

    private Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Src\CacheWarmer $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->eventDispatcher = new Fixtures\Classes\DummyEventDispatcher();
        $this->subject = new Src\CacheWarmer(client: $this->client, eventDispatcher: $this->eventDispatcher);
    }

    /**
     * @param list<string> $urls
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('runCrawlsListOfUrlsDataProvider')]
    public function runCrawlsListOfUrls(array $urls): void
    {
        foreach ($urls as $url) {
            $this->mockHandler->append(new Psr7\Response());
            $this->subject->addUrl($url);
        }

        $result = $this->subject->run();
        $processedUrls = $this->getProcessedUrlsFromCacheWarmupResult($result);

        self::assertSame([], array_diff($urls, $processedUrls));
    }

    #[Framework\Attributes\Test]
    public function runPreparesUrlsWithConfiguredStrategy(): void
    {
        $crawler = new Fixtures\Classes\DummyCrawler($this->eventDispatcher);
        $subject = new Src\CacheWarmer(crawler: $crawler, strategy: new Src\Crawler\Strategy\SortByPriorityStrategy());

        $url1 = new Src\Sitemap\Url('https://www.example.org/foo', 0.75);
        $url2 = new Src\Sitemap\Url('https://www.example.org/', 0.5);
        $url3 = new Src\Sitemap\Url('https://www.example.org/baz', 1.0);

        foreach ([$url1, $url2, $url3] as $url) {
            $subject->addUrl($url);
        }

        $subject->run();

        self::assertSame([$url3, $url1, $url2], $crawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function runDispatchesUrlsPreparedEvent(): void
    {
        $crawler = new Fixtures\Classes\DummyCrawler($this->eventDispatcher);
        $subject = new Src\CacheWarmer(
            crawler: $crawler,
            strategy: new Src\Crawler\Strategy\SortByPriorityStrategy(),
            eventDispatcher: $this->eventDispatcher,
        );

        $url1 = new Src\Sitemap\Url('https://www.example.org/foo', 0.75);
        $url2 = new Src\Sitemap\Url('https://www.example.org/', 0.5);
        $url3 = new Src\Sitemap\Url('https://www.example.org/baz', 1.0);

        foreach ([$url1, $url2, $url3] as $url) {
            $subject->addUrl($url);
        }

        $subject->run();

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\UrlsPrepared::class));
    }

    #[Framework\Attributes\Test]
    public function runDispatchesCrawlingStartedEvent(): void
    {
        $this->subject->run();

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\CrawlingStarted::class));
    }

    #[Framework\Attributes\Test]
    public function runDispatchesCrawlingFinishedEvent(): void
    {
        $this->subject->run();

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\CrawlingFinished::class));
    }

    #[Framework\Attributes\Test]
    public function addSitemapsThrowsExceptionIfInvalidSitemapsAreGiven(): void
    {
        $this->expectException(Src\Exception\SitemapIsInvalid::class);
        $this->expectExceptionCode(1604055096);
        $this->expectExceptionMessage(sprintf('Sitemaps must be of type string or %s, bool given.', Src\Sitemap\Sitemap::class));

        $this->subject->addSitemaps([false]);
    }

    #[Framework\Attributes\Test]
    public function addSitemapsThrowsExceptionIfGivenSitemapCannotBeParsedAndStrictModeIsEnabled(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $sitemap = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($sitemap),
        );

        $this->subject->addSitemaps([$sitemap]);
    }

    #[Framework\Attributes\Test]
    public function addSitemapsDispatchesSitemapParsingFailedEventIfGivenSitemapCannotBeParsed(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $subject = new Src\CacheWarmer(
            client: $this->client,
            strict: false,
            eventDispatcher: $this->eventDispatcher,
        );

        $subject->addSitemaps('https://www.example.com/sitemap.xml');

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\SitemapParsingFailed::class));
    }

    #[Framework\Attributes\Test]
    public function addSitemapsIgnoresParserErrorsIfStrictModeIsDisabled(): void
    {
        $subject = new Src\CacheWarmer(client: $this->client, strict: false);
        $sitemap = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->mockSitemapRequest('invalid_sitemap_1');

        $subject->addSitemaps([$sitemap]);

        self::assertSame([$sitemap], $subject->getFailedSitemaps());
        self::assertSame([], $subject->getSitemaps());
        self::assertSame([], $subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addSitemapsIgnoresSitemapsIfLimitWasExceeded(): void
    {
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));

        $subject = new Src\CacheWarmer(limit: 1, client: $this->client);
        $expected = [
            new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
        ];

        $this->mockSitemapRequest('valid_sitemap_2');

        // Add sitemap (first time)
        $subject->addSitemaps('https://www.example.org/sitemap.xml');
        self::assertEquals($expected, $subject->getUrls());

        // Add sitemap (second time)
        $subject->addSitemaps('https://www.example.com/sitemap.xml');
        self::assertEquals($expected, $subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addSitemapsIgnoresSitemapsIfExcludePatternMatches(): void
    {
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));

        $subject = new Src\CacheWarmer(
            client: $this->client,
            excludePatterns: [
                Src\Config\Option\ExcludePattern::createFromPattern('*/foo'),
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#www\\.example\\.com#'),
            ],
        );

        $this->mockSitemapRequest('valid_sitemap_2');

        $subject->addSitemaps('https://www.example.org/sitemap.xml');
        $subject->addSitemaps('https://www.example.com/sitemap.xml');

        self::assertEquals(
            [
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml')),
            ],
            $subject->getExcludedSitemaps(),
        );
        self::assertEquals(
            [
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
            ],
            $subject->getExcludedUrls(),
        );
        self::assertEquals(
            [
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            ],
            $subject->getSitemaps(),
        );
        self::assertEquals(
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
            ],
            $subject->getUrls(),
        );
    }

    #[Framework\Attributes\Test]
    public function addSitemapsDispatchesSitemapExcludedEventIfExcludePatternMatches(): void
    {
        $subject = new Src\CacheWarmer(
            client: $this->client,
            excludePatterns: [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#www\\.example\\.com#'),
            ],
            eventDispatcher: $this->eventDispatcher,
        );

        $subject->addSitemaps('https://www.example.com/sitemap.xml');

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\SitemapExcluded::class));
    }

    /**
     * @param list<string|Src\Sitemap\Sitemap>|string|Src\Sitemap\Sitemap $sitemaps
     * @param list<Src\Sitemap\Sitemap>                                   $expectedSitemaps
     * @param list<Src\Sitemap\Url>                                       $expectedUrls
     * @param list<string>                                                $prophesizedRequests
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addSitemapsAddsAndParsesGivenSitemapsDataProvider')]
    public function addSitemapsAddsAndParsesGivenSitemaps(
        array|string|Src\Sitemap\Sitemap $sitemaps,
        array $expectedSitemaps,
        array $expectedUrls,
        array $prophesizedRequests = [],
    ): void {
        foreach ($prophesizedRequests as $fixture) {
            $this->mockSitemapRequest($fixture);
        }

        $this->subject->addSitemaps($sitemaps);

        self::assertEquals($expectedSitemaps, $this->subject->getSitemaps());
        self::assertEquals($expectedUrls, $this->subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addSitemapsDispatchesSitemapParsedEvent(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');

        $this->subject->addSitemaps('https://www.example.org/sitemap.xml');

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\SitemapParsed::class));
        self::assertSame(1, $this->eventDispatcher->numberOfDispatchedEventsFor(Src\Event\SitemapAdded::class));
        self::assertSame(3, $this->eventDispatcher->numberOfDispatchedEventsFor(Src\Event\UrlAdded::class));
    }

    #[Framework\Attributes\Test]
    public function addUrlAddsGivenUrlToListOfUrls(): void
    {
        $url = new Src\Sitemap\Url('https://www.example.org/sitemap.xml');

        self::assertSame([$url], $this->subject->addUrl($url)->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addUrlDispatchesUrlExcludedEventIfExcludePatternMatches(): void
    {
        $subject = new Src\CacheWarmer(
            client: $this->client,
            excludePatterns: [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#www\\.example\\.com#'),
            ],
            eventDispatcher: $this->eventDispatcher,
        );

        $subject->addUrl('https://www.example.com/');

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\UrlExcluded::class));
    }

    #[Framework\Attributes\Test]
    public function addUrlDoesNotAddAlreadyAvailableUrlToListOfUrls(): void
    {
        $url = new Src\Sitemap\Url('https://www.example.org/sitemap.xml');

        self::assertSame([$url], $this->subject->addUrl($url)->addUrl($url)->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addUrlDoesNotAddUrlIfLimitWasExceeded(): void
    {
        $url1 = new Src\Sitemap\Url('https://www.example.org/sitemap.xml');
        $url2 = new Src\Sitemap\Url('https://www.example.com/sitemap.xml');

        $subject = new Src\CacheWarmer(limit: 1, client: $this->client);
        $subject->addUrl($url1)->addUrl($url2);

        self::assertSame([$url1], $subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function getLimitReturnsUrlLimit(): void
    {
        self::assertSame(0, $this->subject->getLimit());
    }

    /**
     * @return Generator<string, array{array<int, string>}>
     */
    public static function runCrawlsListOfUrlsDataProvider(): Generator
    {
        yield 'no urls' => [
            [],
        ];
        yield 'multiple urls' => [
            [
                'https://www.example.org',
                'https://www.example.com',
            ],
        ];
    }

    /**
     * @return Generator<string, array{
     *     0: list<string|Src\Sitemap\Sitemap>|string|Src\Sitemap\Sitemap,
     *     1: list<Src\Sitemap\Sitemap>,
     *     2: list<Src\Sitemap\Url>,
     *     3?: list<string>,
     * }>
     */
    public static function addSitemapsAddsAndParsesGivenSitemapsDataProvider(): Generator
    {
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));
        $origin2 = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));
        $origin3 = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap_en.xml'), origin: $origin2);

        $localFile = Src\Helper\FilesystemHelper::joinPathSegments(__DIR__.'/Fixtures/Sitemaps/valid_sitemap_2.xml');
        $originLocal = Src\Sitemap\Sitemap::createFromString($localFile);

        yield 'empty sitemaps' => [
            [],
            [],
            [],
        ];
        yield 'one sitemap url' => [
            'https://www.example.org/sitemap.xml',
            [
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
            ],
            [
                'valid_sitemap_2',
            ],
        ];
        yield 'one sitemap object' => [
            new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            [
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
            ],
            [
                'valid_sitemap_2',
            ],
        ];
        yield 'multiple sitemap urls' => [
            [
                'https://www.example.org/sitemap.xml',
                'https://www.example.com/sitemap.xml',
            ],
            [
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
                new Src\Sitemap\Url('https://www.example.com/', origin: $origin2),
                new Src\Sitemap\Url('https://www.example.com/foo', origin: $origin2),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_3',
            ],
        ];
        yield 'multiple sitemap objects' => [
            [
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
                new Src\Sitemap\Url('https://www.example.com/', origin: $origin2),
                new Src\Sitemap\Url('https://www.example.com/foo', origin: $origin2),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_3',
            ],
        ];
        yield 'mix of sitemap url set and sitemap index' => [
            [
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
                new Src\Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap_en.xml'), origin: $origin2),
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $origin),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $origin),
                new Src\Sitemap\Url('https://www.example.com/', origin: $origin3),
                new Src\Sitemap\Url('https://www.example.com/foo', origin: $origin3),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_1',
                'valid_sitemap_3',
            ],
        ];
        yield 'local sitemap file' => [
            [
                $originLocal,
            ],
            [
                $originLocal,
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $originLocal),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $originLocal),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $originLocal),
            ],
            [],
        ];
        yield 'local sitemap object' => [
            [
                $localFile,
            ],
            [
                $originLocal,
            ],
            [
                new Src\Sitemap\Url('https://www.example.org/', origin: $originLocal),
                new Src\Sitemap\Url('https://www.example.org/foo', origin: $originLocal),
                new Src\Sitemap\Url('https://www.example.org/baz', origin: $originLocal),
            ],
            [],
        ];
    }

    protected function tearDown(): void
    {
        $this->closeStreams();
    }

    private static function getExpectedUri(string $url): Psr7\Uri
    {
        $uri = new Psr7\Uri($url);

        // Due to a new introduced behavior in guzzlehttp/psr7 2.0.0,
        // we have to call the __toString() method in order to explicitly
        // create first-level caches within the instance. Otherwise,
        // self::assertEquals() will fail,
        // see https://github.com/guzzle/psr7/pull/293
        $uri->__toString();

        return $uri;
    }
}
