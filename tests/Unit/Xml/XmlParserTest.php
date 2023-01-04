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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Xml;

use DateTimeImmutable;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\CacheWarmup\Xml;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * XmlParserTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParserTest extends Framework\TestCase
{
    use Tests\Unit\ClientMockTrait;

    private Sitemap\Sitemap $sitemap;
    private Xml\XmlParser $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->sitemap = new Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));
        $this->subject = new Xml\XmlParser($this->client);
    }

    /**
     * @test
     */
    public function parseParsesSitemapIndex(): void
    {
        $this->mockSitemapRequest('valid_sitemap_4');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Sitemap\Sitemap(
                uri: new Psr7\Uri('https://www.example.org/sitemap_en.xml'),
                lastModificationDate: new DateTimeImmutable('2022-08-17T13:18:06+02:00'),
            ),
        ];

        self::assertEquals($expected, $result->getSitemaps());
        self::assertSame([], $result->getUrls());
    }

    /**
     * @test
     */
    public function parseParsesSitemapUrlSet(): void
    {
        $this->mockSitemapRequest('valid_sitemap_5');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Sitemap\Url(
                uri: 'https://www.example.org/',
                priority: 0.8,
                lastModificationDate: new DateTimeImmutable('2022-05-02T12:14:44+02:00'),
                changeFrequency: Sitemap\ChangeFrequency::Yearly,
            ),
            new Sitemap\Url(
                uri: 'https://www.example.org/foo',
                priority: 0.5,
                lastModificationDate: new DateTimeImmutable('2021-06-07T20:01:25+02:00'),
                changeFrequency: Sitemap\ChangeFrequency::Monthly,
            ),
            new Sitemap\Url(
                uri: 'https://www.example.org/baz',
                priority: 0.5,
                lastModificationDate: new DateTimeImmutable('2021-05-28T11:54:00+02:00'),
                changeFrequency: Sitemap\ChangeFrequency::Hourly,
            ),
        ];

        self::assertEquals($expected, $result->getUrls());
        self::assertSame([], $result->getSitemaps());
    }

    /**
     * @test
     */
    public function parseThrowsExceptionOnInvalidSitemapIndex(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $this->expectException(Exception\InvalidSitemapException::class);
        $this->expectExceptionCode(1660668799);
        $this->expectExceptionMessage('The sitemap "https://www.example.org/sitemap.xml" is invalid and cannot be parsed.');

        $this->subject->parse($this->sitemap);
    }

    /**
     * @test
     */
    public function parseThrowsExceptionOnInvalidSitemapUrl(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_2');

        $this->expectException(Exception\InvalidUrlException::class);
        $this->expectExceptionCode(1604055334);
        $this->expectExceptionMessage('The given URL "foo" is not valid.');

        $this->subject->parse($this->sitemap);
    }

    protected function tearDown(): void
    {
        $this->closeStreams();
    }
}
