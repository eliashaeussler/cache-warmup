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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Xml;

use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\CacheWarmup\Tests\Unit\RequestProphecyTrait;
use EliasHaeussler\CacheWarmup\Xml\XmlParser;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * XmlParserTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParserTest extends TestCase
{
    use RequestProphecyTrait;

    /**
     * @var XmlParser
     */
    protected $subject;

    protected function setUp(): void
    {
        $sitemap = new Sitemap(new Uri('https://www.example.org/sitemap.xml'));
        $this->clientProphecy = $this->prophesize(ClientInterface::class);
        $this->subject = new XmlParser($sitemap, $this->clientProphecy->reveal());
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function parseParsesSitemapIndex(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_1');
        $this->subject->parse();

        $expected = [
            new Sitemap(new Uri('https://www.example.org/sitemap_en.xml')),
        ];
        static::assertEquals($expected, $this->subject->getParsedSitemaps());
        static::assertSame([], $this->subject->getParsedUrls());
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function parseParsesSitemapUrlSet(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_2');
        $this->subject->parse();

        $expected = [
            new Uri('https://www.example.org/'),
            new Uri('https://www.example.org/foo'),
            new Uri('https://www.example.org/baz'),
        ];
        static::assertEquals($expected, $this->subject->getParsedUrls());
        static::assertSame([], $this->subject->getParsedSitemaps());
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function parseParsesSitemapIndexAndSkipsInvalidSitemaps(): void
    {
        $this->prophesizeSitemapRequest('invalid_sitemap_1');
        $this->subject->parse();

        $expected = [
            new Sitemap(new Uri('https://www.example.org/sitemap_alt_2.xml')),
        ];
        static::assertEquals($expected, $this->subject->getParsedSitemaps());
        static::assertSame([], $this->subject->getParsedUrls());
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function parseParsesSitemapUrlSetAndSkipsInvalidUrls(): void
    {
        $this->prophesizeSitemapRequest('invalid_sitemap_2');
        $this->subject->parse();

        $expected = [
            new Uri('https://www.example.org/foo'),
            new Uri('https://www.example.org/baz'),
        ];
        static::assertEquals($expected, $this->subject->getParsedUrls());
        static::assertSame([], $this->subject->getParsedSitemaps());
    }

    /**
     * @test
     */
    public function getParsedSitemapsReturnsEmptyArrayIfSitemapHasNotBeenCrawledYet(): void
    {
        static::assertSame([], $this->subject->getParsedSitemaps());
    }

    /**
     * @test
     */
    public function getParsedSitemapsUrlsEmptyArrayIfSitemapHasNotBeenCrawledYet(): void
    {
        static::assertSame([], $this->subject->getParsedUrls());
    }

    protected function tearDown(): void
    {
        $this->closeStream();
    }
}
