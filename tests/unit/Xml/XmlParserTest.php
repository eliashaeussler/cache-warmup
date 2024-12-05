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

namespace EliasHaeussler\CacheWarmup\Tests\Xml;

use DateTimeImmutable;
use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

use function dirname;

/**
 * XmlParserTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Xml\XmlParser::class)]
final class XmlParserTest extends Framework\TestCase
{
    use Tests\ClientMockTrait;

    private Src\Sitemap\Sitemap $sitemap;
    private Src\Xml\XmlParser $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->sitemap = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));
        $this->subject = new Src\Xml\XmlParser($this->client);
    }

    #[Framework\Attributes\Test]
    public function parseParsesSitemapIndex(): void
    {
        $this->mockSitemapRequest('valid_sitemap_4');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Src\Sitemap\Sitemap(
                uri: new Psr7\Uri('https://www.example.org/sitemap_en.xml'),
                lastModificationDate: new DateTimeImmutable('2022-08-17T13:18:06+02:00'),
                origin: $this->sitemap,
            ),
        ];

        self::assertEquals($expected, $result->getSitemaps());
        self::assertSame([], $result->getUrls());
    }

    #[Framework\Attributes\Test]
    public function parseParsesSitemapUrlSet(): void
    {
        $this->mockSitemapRequest('valid_sitemap_5');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Src\Sitemap\Url(
                uri: 'https://www.example.org/',
                priority: 0.8,
                lastModificationDate: new DateTimeImmutable('2022-05-02T00:00:00+00:00'),
                changeFrequency: Src\Sitemap\ChangeFrequency::Yearly,
                origin: $this->sitemap,
            ),
            new Src\Sitemap\Url(
                uri: 'https://www.example.org/foo',
                priority: 0.5,
                lastModificationDate: new DateTimeImmutable('2021-06-07T20:01:25+00:00'),
                changeFrequency: Src\Sitemap\ChangeFrequency::Monthly,
                origin: $this->sitemap,
            ),
            new Src\Sitemap\Url(
                uri: 'https://www.example.org/baz',
                priority: 0.5,
                lastModificationDate: new DateTimeImmutable('2021-05-28T11:54:00+02:00'),
                changeFrequency: Src\Sitemap\ChangeFrequency::Hourly,
                origin: $this->sitemap,
            ),
        ];

        self::assertEquals($expected, $result->getUrls());
        self::assertSame([], $result->getSitemaps());
    }

    #[Framework\Attributes\Test]
    public function parseParsesGzippedSitemap(): void
    {
        $this->mockSitemapRequest('valid_sitemap_6', 'xml.gz');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Src\Sitemap\Url('https://www.example.com/', origin: $this->sitemap),
            new Src\Sitemap\Url('https://www.example.com/foo', origin: $this->sitemap),
        ];

        self::assertEquals($expected, $result->getUrls());
    }

    #[Framework\Attributes\Test]
    public function parseFollowsRedirects(): void
    {
        $this->mockHandler->append(new Psr7\Response(301, ['Location' => 'https://www.example.org/sub/sitemap.xml']));

        $this->mockSitemapRequest('valid_sitemap_5');

        $result = $this->subject->parse($this->sitemap);

        self::assertNotEmpty($result->getUrls());
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnInvalidXml(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_3');

        $this->expectExceptionObject(
            new Src\Exception\SitemapCannotBeRead($this->sitemap),
        );

        $this->subject->parse($this->sitemap);
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnInvalidSitemapIndex(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->sitemap),
        );

        $this->subject->parse($this->sitemap);
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnInvalidSitemapUrl(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_2');

        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed($this->sitemap),
        );

        $this->subject->parse($this->sitemap);
    }

    #[Framework\Attributes\Test]
    public function parseParsesLocalFile(): void
    {
        $filename = Src\Helper\FilesystemHelper::joinPathSegments(
            dirname(__DIR__).'/Fixtures/Sitemaps/valid_sitemap_4.xml',
        );
        $sitemap = Src\Sitemap\Sitemap::createFromString($filename);

        $result = $this->subject->parse($sitemap);

        $expected = [
            new Src\Sitemap\Sitemap(
                uri: new Psr7\Uri('https://www.example.org/sitemap_en.xml'),
                lastModificationDate: new DateTimeImmutable('2022-08-17T13:18:06+02:00'),
                origin: $sitemap,
            ),
        ];

        self::assertEquals($expected, $result->getSitemaps());
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnMissingLocalFile(): void
    {
        $sitemap = Src\Sitemap\Sitemap::createFromString('/foo');

        $this->expectException(Src\Exception\FileIsMissing::class);
        $this->expectExceptionCode(1698427082);
        $this->expectExceptionMessage('The file "/foo" does not exist or is not readable');

        $this->subject->parse($sitemap);
    }

    protected function tearDown(): void
    {
        $this->closeStreams();
    }
}
