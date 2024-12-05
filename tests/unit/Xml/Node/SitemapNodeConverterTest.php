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

namespace EliasHaeussler\CacheWarmup\Tests\Xml\Node;

use DateTimeImmutable;
use DateTimeZone;
use EliasHaeussler\CacheWarmup as Src;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * SitemapNodeConverterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Xml\Node\SitemapNodeConverter::class)]
final class SitemapNodeConverterTest extends Framework\TestCase
{
    private Src\Xml\Node\SitemapNodeConverter $subject;
    private Src\Sitemap\Sitemap $origin;

    public function setUp(): void
    {
        $this->subject = new Src\Xml\Node\SitemapNodeConverter();
        $this->origin = Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml');
    }

    #[Framework\Attributes\Test]
    public function convertSitemapThrowsExceptionOnMissingLocation(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->origin),
        );

        $this->subject->convertSitemap([], $this->origin);
    }

    #[Framework\Attributes\Test]
    public function convertSitemapThrowsExceptionOnInvalidLocation(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->origin),
        );

        $this->subject->convertSitemap(['loc' => 'foo'], $this->origin);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('convertSitemapSupportsMultipleDateTimeFormatsDataProvider')]
    public function convertSitemapSupportsMultipleDateTimeFormats(string $datetime, ?DateTimeImmutable $expected): void
    {
        $node = [
            'loc' => 'https://www.example.com/',
            'lastmod' => $datetime,
        ];

        self::assertEquals(
            new Src\Sitemap\Sitemap(
                new Psr7\Uri('https://www.example.com/'),
                $expected,
                $this->origin,
            ),
            $this->subject->convertSitemap($node, $this->origin),
        );
    }

    #[Framework\Attributes\Test]
    public function convertSitemapReturnsConvertedSitemapObject(): void
    {
        $node = [
            'loc' => 'https://www.example.com/',
            'lastmod' => '2023-12-31',
        ];

        $expected = new Src\Sitemap\Sitemap(
            new Psr7\Uri('https://www.example.com/'),
            new DateTimeImmutable('2023-12-31 00:00:00'),
            $this->origin,
        );

        self::assertEquals($expected, $this->subject->convertSitemap($node, $this->origin));
    }

    #[Framework\Attributes\Test]
    public function convertUrlThrowsExceptionOnMissingLocation(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->origin),
        );

        $this->subject->convertUrl([], $this->origin);
    }

    #[Framework\Attributes\Test]
    public function convertUrlThrowsExceptionOnInvalidLocation(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->origin),
        );

        $this->subject->convertUrl(['loc' => 'foo'], $this->origin);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('convertUrlSupportsMultipleDateTimeFormatsDataProvider')]
    public function convertUrlSupportsMultipleDateTimeFormats(string $datetime, ?DateTimeImmutable $expected): void
    {
        $node = [
            'loc' => 'https://www.example.com/',
            'lastmod' => $datetime,
        ];

        self::assertEquals(
            new Src\Sitemap\Url(
                'https://www.example.com/',
                lastModificationDate: $expected,
                origin: $this->origin,
            ),
            $this->subject->convertUrl($node, $this->origin),
        );
    }

    #[Framework\Attributes\Test]
    public function convertUrlReturnsConvertedUrlObject(): void
    {
        $node = [
            'loc' => 'https://www.example.com/',
            'lastmod' => '2023-12-31',
            'changefreq' => 'daily',
            'priority' => '0.7',
        ];

        $expected = new Src\Sitemap\Url(
            'https://www.example.com/',
            0.7,
            new DateTimeImmutable('2023-12-31 00:00:00'),
            Src\Sitemap\ChangeFrequency::Daily,
            $this->origin,
        );

        self::assertEquals($expected, $this->subject->convertUrl($node, $this->origin));
    }

    /**
     * @return Generator<string, array{string, DateTimeImmutable|null}>
     */
    public static function convertSitemapSupportsMultipleDateTimeFormatsDataProvider(): Generator
    {
        yield 'W3C' => [
            '2023-12-31T12:34:56+00:00',
            new DateTimeImmutable('2023-12-31 12:34:56', new DateTimeZone('UTC')),
        ];
        yield 'date and time' => [
            '2023-12-31T12:34:56.000Z',
            new DateTimeImmutable('2023-12-31 12:34:56'),
        ];
        yield 'date only' => [
            '2023-12-31',
            new DateTimeImmutable('2023-12-31 00:00:00'),
        ];
        yield 'unsupported format' => [
            '2023-12-31T12:34:56',
            null,
        ];
        yield 'invalid datetime' => [
            // null byte raises a ValueError
            '2023-12-\01',
            null,
        ];
    }

    /**
     * @return Generator<string, array{string, DateTimeImmutable|null}>
     */
    public static function convertUrlSupportsMultipleDateTimeFormatsDataProvider(): Generator
    {
        yield 'W3C' => [
            '2023-12-31T12:34:56+00:00',
            new DateTimeImmutable('2023-12-31 12:34:56', new DateTimeZone('UTC')),
        ];
        yield 'date and time' => [
            '2023-12-31T12:34:56.000Z',
            new DateTimeImmutable('2023-12-31 12:34:56'),
        ];
        yield 'date only' => [
            '2023-12-31',
            new DateTimeImmutable('2023-12-31 00:00:00'),
        ];
        yield 'unsupported format' => [
            '2023-12-31T12:34:56',
            null,
        ];
        yield 'invalid datetime' => [
            // null byte raises a ValueError
            '2023-12-\01',
            null,
        ];
    }
}
