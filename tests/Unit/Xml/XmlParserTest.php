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
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\CacheWarmup\Xml;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Prophecy\PhpUnit;
use Psr\Http\Client;

/**
 * XmlParserTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParserTest extends Framework\TestCase
{
    use PhpUnit\ProphecyTrait;
    use Tests\Unit\RequestProphecyTrait;

    private Sitemap $sitemap;
    private Xml\XmlParser $subject;

    protected function setUp(): void
    {
        $this->sitemap = new Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml'));
        $this->clientProphecy = $this->prophesize(Client\ClientInterface::class);
        $this->subject = new Xml\XmlParser($this->clientProphecy->reveal());
    }

    /**
     * @test
     */
    public function parseParsesSitemapIndex(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_1');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Sitemap(new Psr7\Uri('https://www.example.org/sitemap_en.xml')),
        ];

        self::assertEquals($expected, $result->getSitemaps());
        self::assertSame([], $result->getUrls());
    }

    /**
     * @test
     */
    public function parseParsesSitemapUrlSet(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_2');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Psr7\Uri('https://www.example.org/'),
            new Psr7\Uri('https://www.example.org/foo'),
            new Psr7\Uri('https://www.example.org/baz'),
        ];

        self::assertEquals($expected, $result->getUrls());
        self::assertSame([], $result->getSitemaps());
    }

    /**
     * @test
     */
    public function parseParsesSitemapIndexAndSkipsInvalidSitemaps(): void
    {
        $this->prophesizeSitemapRequest('invalid_sitemap_1');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Sitemap(new Psr7\Uri('https://www.example.org/sitemap_alt_2.xml')),
        ];

        self::assertEquals($expected, $result->getSitemaps());
        self::assertSame([], $result->getUrls());
    }

    /**
     * @test
     */
    public function parseParsesSitemapUrlSetAndSkipsInvalidUrls(): void
    {
        $this->prophesizeSitemapRequest('invalid_sitemap_2');

        $result = $this->subject->parse($this->sitemap);

        $expected = [
            new Psr7\Uri('https://www.example.org/foo'),
            new Psr7\Uri('https://www.example.org/baz'),
        ];

        self::assertEquals($expected, $result->getUrls());
        self::assertSame([], $result->getSitemaps());
    }

    protected function tearDown(): void
    {
        $this->closeStreams();
    }
}
