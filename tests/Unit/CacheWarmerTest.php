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

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;

/**
 * CacheWarmerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmerTest extends TestCase
{
    use ProphecyTrait;
    use RequestProphecyTrait;
    use CrawlerResultProcessorTrait;

    /**
     * @var CacheWarmer
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->clientProphecy = $this->prophesize(ClientInterface::class);
        $this->subject = new CacheWarmer(null, 0, $this->clientProphecy->reveal());
    }

    /**
     * @test
     * @dataProvider runCrawlsListOfUrlsDataProvider
     *
     * @param Uri[] $urls
     */
    public function runCrawlsListOfUrls(array $urls): void
    {
        foreach ($urls as $url) {
            $this->subject->addUrl($url);
        }
        $crawler = $this->subject->run();
        $processedUrls = $this->getProcessedUrlsFromCrawler($crawler);
        self::assertTrue([] === array_diff($urls, $processedUrls));
    }

    /**
     * @test
     */
    public function addSitemapsThrowsExceptionIfGivenSitemapIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1604055264);
        $this->subject->addSitemaps('');
    }

    /**
     * @test
     */
    public function addSitemapsThrowsExceptionIfGivenSitemapIsNotValid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1604055334);
        $this->subject->addSitemaps(['foo']);
    }

    /**
     * @test
     */
    public function addSitemapsThrowsExceptionIfInvalidSitemapsIsGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1604055096);

        /* @phpstan-ignore-next-line */
        $this->subject->addSitemaps([false]);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function addSitemapsIgnoresSitemapsIfLimitWasExceeded(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_2');
        $expected = [new Uri('https://www.example.org/')];

        // Set URL limit
        $this->subject->setLimit(1);

        // Add sitemap (first time)
        $this->subject->addSitemaps('https://www.example.org/sitemap.xml');
        self::assertEquals($expected, $this->subject->getUrls());

        // Add sitemap (second time)
        $this->subject->addSitemaps('https://www.example.com/sitemap.xml');
        self::assertEquals($expected, $this->subject->getUrls());
    }

    /**
     * @test
     * @dataProvider addSitemapsAddsAndParsesGivenSitemapsDataProvider
     *
     * @param string[]|Sitemap[]|string|Sitemap|null $sitemaps
     * @param Sitemap[]                              $expectedSitemaps
     * @param UriInterface[]                         $expectedUrls
     * @param array<string, UriInterface>            $prophesizedRequests
     *
     * @throws ClientExceptionInterface
     */
    public function addSitemapsAddsAndParsesGivenSitemaps(
        $sitemaps,
        array $expectedSitemaps,
        array $expectedUrls,
        array $prophesizedRequests = []
    ): void {
        foreach ($prophesizedRequests as $fixture => $expectedUri) {
            $this->prophesizeSitemapRequest($fixture, $expectedUri);
        }
        $this->subject->addSitemaps($sitemaps);
        self::assertEquals($expectedSitemaps, $this->subject->getSitemaps());
        self::assertEquals($expectedUrls, $this->subject->getUrls());
    }

    /**
     * @test
     */
    public function addUrlAddsGivenUrlToListOfUrls(): void
    {
        $url = $this->getExpectedUri('https://www.example.org/sitemap.xml');
        self::assertSame([$url], $this->subject->addUrl($url)->getUrls());
    }

    /**
     * @test
     */
    public function addUrlDoesNotAddAlreadyAvailableUrlToListOfUrls(): void
    {
        $url = $this->getExpectedUri('https://www.example.org/sitemap.xml');
        self::assertSame([$url], $this->subject->addUrl($url)->addUrl($url)->getUrls());
    }

    /**
     * @test
     */
    public function addUrlDoesNotAddUrlIfLimitWasExceeded(): void
    {
        $url1 = $this->getExpectedUri('https://www.example.org/sitemap.xml');
        $url2 = $this->getExpectedUri('https://www.example.com/sitemap.xml');

        $this->subject->setLimit(1);
        $this->subject->addUrl($url1)->addUrl($url2);

        self::assertSame([$url1], $this->subject->getUrls());
    }

    /**
     * @test
     */
    public function getLimitReturnsUrlLimit(): void
    {
        self::assertSame(0, $this->subject->getLimit());
        self::assertSame(10, $this->subject->setLimit(10)->getLimit());
    }

    /**
     * @test
     */
    public function setLimitDefinesUrlLimit(): void
    {
        $this->subject->setLimit(10);
        self::assertSame(10, $this->subject->getLimit());
    }

    /**
     * @return array<string, array{array<int, Uri>}>
     */
    public function runCrawlsListOfUrlsDataProvider(): array
    {
        return [
            'no urls' => [
                [],
            ],
            'multiple urls' => [
                [
                    $this->getExpectedUri('https://www.example.org'),
                    $this->getExpectedUri('https://www.example.com'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function addSitemapsAddsAndParsesGivenSitemapsDataProvider(): array
    {
        return [
            'no sitemaps' => [
                null,
                [],
                [],
            ],
            'empty sitemaps' => [
                [],
                [],
                [],
            ],
            'one sitemap url' => [
                'https://www.example.org/sitemap.xml',
                [
                    new Sitemap(new Uri('https://www.example.org/sitemap.xml')),
                ],
                [
                    new Uri('https://www.example.org/'),
                    new Uri('https://www.example.org/foo'),
                    new Uri('https://www.example.org/baz'),
                ],
                [
                    'valid_sitemap_2' => null,
                ],
            ],
            'one sitemap object' => [
                new Sitemap(new Uri('https://www.example.org/sitemap.xml')),
                [
                    new Sitemap(new Uri('https://www.example.org/sitemap.xml')),
                ],
                [
                    new Uri('https://www.example.org/'),
                    new Uri('https://www.example.org/foo'),
                    new Uri('https://www.example.org/baz'),
                ],
                [
                    'valid_sitemap_2' => null,
                ],
            ],
            'multiple sitemap urls' => [
                [
                    'https://www.example.org/sitemap.xml',
                    'https://www.example.com/sitemap.xml',
                ],
                [
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.com/sitemap.xml')),
                ],
                [
                    new Uri('https://www.example.org/'),
                    new Uri('https://www.example.org/foo'),
                    new Uri('https://www.example.org/baz'),
                    new Uri('https://www.example.com/'),
                    new Uri('https://www.example.com/foo'),
                ],
                [
                    'valid_sitemap_2' => new Uri('https://www.example.org/sitemap.xml'),
                    'valid_sitemap_3' => new Uri('https://www.example.com/sitemap.xml'),
                ],
            ],
            'multiple sitemap objects' => [
                [
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.com/sitemap.xml')),
                ],
                [
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.com/sitemap.xml')),
                ],
                [
                    new Uri('https://www.example.org/'),
                    new Uri('https://www.example.org/foo'),
                    new Uri('https://www.example.org/baz'),
                    new Uri('https://www.example.com/'),
                    new Uri('https://www.example.com/foo'),
                ],
                [
                    'valid_sitemap_2' => new Uri('https://www.example.org/sitemap.xml'),
                    'valid_sitemap_3' => new Uri('https://www.example.com/sitemap.xml'),
                ],
            ],
            'mix of sitemap url set and sitemap index' => [
                [
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.com/sitemap.xml')),
                ],
                [
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.com/sitemap.xml')),
                    new Sitemap($this->getExpectedUri('https://www.example.org/sitemap_en.xml')),
                ],
                [
                    new Uri('https://www.example.org/'),
                    new Uri('https://www.example.org/foo'),
                    new Uri('https://www.example.org/baz'),
                    new Uri('https://www.example.com/'),
                    new Uri('https://www.example.com/foo'),
                ],
                [
                    'valid_sitemap_2' => new Uri('https://www.example.org/sitemap.xml'),
                    'valid_sitemap_1' => new Uri('https://www.example.com/sitemap.xml'),
                    'valid_sitemap_3' => new Uri('https://www.example.org/sitemap_en.xml'),
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $this->closeStream();
    }

    private function getExpectedUri(string $url): Uri
    {
        $uri = new Uri($url);

        // Due to a new introduced behavior in guzzlehttp/psr7 2.0.0,
        // we have to call the __toString() method in order to explicitly
        // create first-level caches within the instance. Otherwise,
        // self::assertEquals() will fail,
        // see https://github.com/guzzle/psr7/pull/293
        $uri->__toString();

        return $uri;
    }
}
