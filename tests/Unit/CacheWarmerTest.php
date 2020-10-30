<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Tests\Unit;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * CacheWarmerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class CacheWarmerTest extends TestCase
{
    use RequestProphecyTrait;

    /**
     * @var CacheWarmer
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->clientProphecy = $this->prophesize(ClientInterface::class);
        $this->subject = new CacheWarmer(null, $this->clientProphecy->reveal());
    }

    /**
     * @test
     * @dataProvider runCrawlsListOfUrlsDataProvider
     * @param Uri[] $urls
     */
    public function runCrawlsListOfUrls(array $urls): void
    {
        foreach ($urls as $url) {
            $this->subject->addUrl($url);
        }

        $crawler = $this->subject->run();
        $processedUrls = array_merge($crawler->getSuccessfulUrls(), $crawler->getFailedUrls());
        static::assertTrue(array_diff($urls, array_column($processedUrls, 'url')) === []);
    }

    /**
     * @test
     * @dataProvider addSitemapsAddsAndParsesGivenSitemapsDataProvider
     * @param string[]|Sitemap[]|string|Sitemap|null $sitemaps
     * @param array $expected
     * @param array $prophesizedRequests
     * @throws ClientExceptionInterface
     */
    public function addSitemapsAddsAndParsesGivenSitemaps($sitemaps, array $expected, array $prophesizedRequests = []): void
    {
        foreach ($prophesizedRequests as $fixture => $expectedUri) {
            $this->prophesizeSitemapRequest($fixture, $expectedUri);
        }
        $this->subject->addSitemaps($sitemaps);
        static::assertEquals($expected, $this->subject->getUrls());
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
        $this->subject->addSitemaps([false]);
    }

    /**
     * @test
     */
    public function addUrlAddsGivenUrlToListOfUrls(): void
    {
        $url = new Uri('https://www.example.org/sitemap.xml');
        static::assertSame([$url], $this->subject->addUrl($url)->getUrls());
    }

    /**
     * @test
     */
    public function addUrlDoesNotAddAlreadyAvailableUrlToListOfUrls(): void
    {
        $url = new Uri('https://www.example.org/sitemap.xml');
        static::assertSame([$url], $this->subject->addUrl($url)->addUrl($url)->getUrls());
    }

    public function runCrawlsListOfUrlsDataProvider(): array
    {
        return [
            'no urls' => [
                [],
            ],
            'multiple urls' => [
                [
                    new Uri('https://www.example.org'),
                    new Uri('https://www.example.com'),
                ]
            ],
        ];
    }

    public function addSitemapsAddsAndParsesGivenSitemapsDataProvider(): array
    {
        return [
            'no sitemaps' => [
                null,
                [],
            ],
            'empty sitemaps' => [
                [],
                [],
            ],
            'one sitemap url' => [
                'https://www.example.org/sitemap.xml',
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
                    new Sitemap(new Uri('https://www.example.org/sitemap.xml')),
                    new Sitemap(new Uri('https://www.example.com/sitemap.xml')),
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
                    new Sitemap(new Uri('https://www.example.org/sitemap.xml')),
                    new Sitemap(new Uri('https://www.example.com/sitemap.xml')),
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
}
