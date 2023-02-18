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

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Sitemap;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

use function implode;
use function sprintf;

/**
 * CacheWarmerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmerTest extends Framework\TestCase
{
    use CacheWarmupResultProcessorTrait;
    use ClientMockTrait;

    private CacheWarmer $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->subject = new CacheWarmer(client: $this->client);
    }

    /**
     * @param list<string> $urls
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('runCrawlsListOfUrlsDataProvider')]
    public function runCrawlsListOfUrls(array $urls): void
    {
        foreach ($urls as $url) {
            $this->subject->addUrl($url);
        }

        $result = $this->subject->run();
        $processedUrls = $this->getProcessedUrlsFromCacheWarmupResult($result);

        self::assertSame([], array_diff($urls, $processedUrls));
    }

    #[Framework\Attributes\Test]
    public function addSitemapsThrowsExceptionIfInvalidSitemapsIsGiven(): void
    {
        $this->expectException(Exception\InvalidSitemapException::class);
        $this->expectExceptionCode(1604055096);
        $this->expectExceptionMessage(sprintf('Sitemaps must be of type string or %s, bool given.', Sitemap\Sitemap::class));

        /* @phpstan-ignore-next-line */
        $this->subject->addSitemaps([false]);
    }

    #[Framework\Attributes\Test]
    public function addSitemapsThrowsExceptionIfGivenSitemapCannotBeParsedAndCacheWarmerIsRunningInStrictMode(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $sitemap = new Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->expectException(Exception\InvalidSitemapException::class);
        $this->expectExceptionCode(1660668799);
        $this->expectExceptionMessage(
            implode(PHP_EOL, [
                'The sitemap "https://www.example.com/sitemap.xml" is invalid and cannot be parsed due to the following errors:',
                '  * The given URL must not be empty.',
            ]),
        );

        $this->subject->addSitemaps([$sitemap]);
    }

    #[Framework\Attributes\Test]
    public function addSitemapsIgnoresParserErrorsIfCacheWarmerIsNotRunningInStrictMode(): void
    {
        $subject = new CacheWarmer(client: $this->client, strict: false);
        $sitemap = new Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->mockSitemapRequest('invalid_sitemap_1');

        $subject->addSitemaps([$sitemap]);

        self::assertSame([$sitemap], $subject->getFailedSitemaps());
        self::assertSame([], $subject->getSitemaps());
        self::assertSame([], $subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addSitemapsIgnoresSitemapsIfLimitWasExceeded(): void
    {
        $subject = new CacheWarmer(limit: 1, client: $this->client);
        $expected = [
            new Sitemap\Url('https://www.example.org/'),
        ];

        $this->mockSitemapRequest('valid_sitemap_2');

        // Add sitemap (first time)
        $subject->addSitemaps('https://www.example.org/sitemap.xml');
        self::assertEquals($expected, $subject->getUrls());

        // Add sitemap (second time)
        $subject->addSitemaps('https://www.example.com/sitemap.xml');
        self::assertEquals($expected, $subject->getUrls());
    }

    /**
     * @param list<string|Sitemap\Sitemap>|string|Sitemap\Sitemap $sitemaps
     * @param list<Sitemap\Sitemap>                               $expectedSitemaps
     * @param list<Sitemap\Url>                                   $expectedUrls
     * @param list<string>                                        $prophesizedRequests
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addSitemapsAddsAndParsesGivenSitemapsDataProvider')]
    public function addSitemapsAddsAndParsesGivenSitemaps(
        array|string|Sitemap\Sitemap $sitemaps,
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
    public function addUrlAddsGivenUrlToListOfUrls(): void
    {
        $url = new Sitemap\Url('https://www.example.org/sitemap.xml');

        self::assertSame([$url], $this->subject->addUrl($url)->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addUrlDoesNotAddAlreadyAvailableUrlToListOfUrls(): void
    {
        $url = new Sitemap\Url('https://www.example.org/sitemap.xml');

        self::assertSame([$url], $this->subject->addUrl($url)->addUrl($url)->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addUrlDoesNotAddUrlIfLimitWasExceeded(): void
    {
        $url1 = new Sitemap\Url('https://www.example.org/sitemap.xml');
        $url2 = new Sitemap\Url('https://www.example.com/sitemap.xml');

        $subject = new CacheWarmer(limit: 1, client: $this->client);
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
     * @return Generator<string, array{0: list<string|Sitemap\Sitemap>|string|Sitemap\Sitemap, 1: list<Sitemap\Sitemap>, 2: list<Sitemap\Url>, 3?: list<string>}>
     */
    public static function addSitemapsAddsAndParsesGivenSitemapsDataProvider(): Generator
    {
        yield 'empty sitemaps' => [
            [],
            [],
            [],
        ];
        yield 'one sitemap url' => [
            'https://www.example.org/sitemap.xml',
            [
                new Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            ],
            [
                new Sitemap\Url('https://www.example.org/'),
                new Sitemap\Url('https://www.example.org/foo'),
                new Sitemap\Url('https://www.example.org/baz'),
            ],
            [
                'valid_sitemap_2',
            ],
        ];
        yield 'one sitemap object' => [
            new Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            [
                new Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
            ],
            [
                new Sitemap\Url('https://www.example.org/'),
                new Sitemap\Url('https://www.example.org/foo'),
                new Sitemap\Url('https://www.example.org/baz'),
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
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Sitemap\Url('https://www.example.org/'),
                new Sitemap\Url('https://www.example.org/foo'),
                new Sitemap\Url('https://www.example.org/baz'),
                new Sitemap\Url('https://www.example.com/'),
                new Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_3',
            ],
        ];
        yield 'multiple sitemap objects' => [
            [
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Sitemap\Url('https://www.example.org/'),
                new Sitemap\Url('https://www.example.org/foo'),
                new Sitemap\Url('https://www.example.org/baz'),
                new Sitemap\Url('https://www.example.com/'),
                new Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_3',
            ],
        ];
        yield 'mix of sitemap url set and sitemap index' => [
            [
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
            ],
            [
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.com/sitemap.xml')),
                new Sitemap\Sitemap(self::getExpectedUri('https://www.example.org/sitemap_en.xml')),
            ],
            [
                new Sitemap\Url('https://www.example.org/'),
                new Sitemap\Url('https://www.example.org/foo'),
                new Sitemap\Url('https://www.example.org/baz'),
                new Sitemap\Url('https://www.example.com/'),
                new Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                'valid_sitemap_2',
                'valid_sitemap_1',
                'valid_sitemap_3',
            ],
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
