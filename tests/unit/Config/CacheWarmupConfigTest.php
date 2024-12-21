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

namespace EliasHaeussler\CacheWarmup\Tests\Config;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Generator;
use PHPUnit\Framework;
use Psr\Log;

/**
 * CacheWarmupConfigTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\CacheWarmupConfig::class)]
final class CacheWarmupConfigTest extends Framework\TestCase
{
    private Src\Config\CacheWarmupConfig $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
            ],
            10,
            true,
            ['foo' => 'baz'],
            Tests\Fixtures\Classes\DummyCrawler::class,
            ['foo' => 'baz'],
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            Tests\Fixtures\Classes\DummyParser::class,
            ['foo' => 'baz'],
            'foo',
            'errors.log',
            Log\LogLevel::DEBUG,
            true,
            true,
            300,
        );
    }

    /**
     * @param list<Src\Sitemap\Sitemap> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addSitemapAddsGivenSitemapToSitemapsDataProvider')]
    public function addSitemapAddsGivenSitemapToSitemaps(Src\Sitemap\Sitemap|string $sitemap, array $expected): void
    {
        $this->subject->addSitemap($sitemap);

        self::assertEquals($expected, $this->subject->getSitemaps());
    }

    /**
     * @param list<Src\Sitemap\Url> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addUrlAddsGivenUrlToUrlsDataProvider')]
    public function addUrlAddsGivenUrlToUrls(Src\Sitemap\Url|string $url, array $expected): void
    {
        $this->subject->addUrl($url);

        self::assertEquals($expected, $this->subject->getUrls());
    }

    #[Framework\Attributes\Test]
    public function addExcludePatternAddsGivenExcludePatternToExcludePatterns(): void
    {
        $this->subject->addExcludePattern(
            Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#'),
        );

        $expected = [
            Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
            Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#'),
        ];

        self::assertEquals($expected, $this->subject->getExcludePatterns());
    }

    #[Framework\Attributes\Test]
    public function setLimitEnforcesNonNegativeInteger(): void
    {
        $this->subject->setLimit(-10);

        self::assertSame(0, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function disableLimitSetsLimitToZero(): void
    {
        $this->subject->disableLimit();

        self::assertSame(0, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function setClientOptionAppliesGivenClientOptionToClientOptions(): void
    {
        $this->subject->setClientOption('baz', 'foo');

        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
        ];

        self::assertSame($expected, $this->subject->getClientOptions());
    }

    #[Framework\Attributes\Test]
    public function removeClientOptionRemovesGivenClientOptionFromClientOptions(): void
    {
        $this->subject->removeClientOption('foo');

        self::assertSame([], $this->subject->getClientOptions());
    }

    #[Framework\Attributes\Test]
    public function setCrawlerOptionAppliesGivenCrawlerOptionToCrawlerOptions(): void
    {
        $this->subject->setCrawlerOption('baz', 'foo');

        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
        ];

        self::assertSame($expected, $this->subject->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function removeCrawlerOptionRemovesGivenCrawlerOptionFromCrawlerOptions(): void
    {
        $this->subject->removeCrawlerOption('foo');

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function setParserOptionAppliesGivenParserOptionToParserOptions(): void
    {
        $this->subject->setParserOption('baz', 'foo');

        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
        ];

        self::assertSame($expected, $this->subject->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function removeParserOptionRemovesGivenParserOptionFromParserOptions(): void
    {
        $this->subject->removeParserOption('foo');

        self::assertSame([], $this->subject->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function useJsonFormatSetsJsonAsConfiguredFormat(): void
    {
        $this->subject->useJsonFormat();

        self::assertSame(Src\Formatter\JsonFormatter::getType(), $this->subject->getFormat());
    }

    #[Framework\Attributes\Test]
    public function useTextFormatSetsTextAsConfiguredFormat(): void
    {
        $this->subject->useTextFormat();

        self::assertSame(Src\Formatter\TextFormatter::getType(), $this->subject->getFormat());
    }

    #[Framework\Attributes\Test]
    public function disableEndlessModeSetsEndlessModeToZero(): void
    {
        $this->subject->disableEndlessMode();

        self::assertSame(0, $this->subject->getRepeatAfter());
    }

    #[Framework\Attributes\Test]
    public function mergeMergesGivenConfigIntoCurrentConfigObject(): void
    {
        $other = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_fr.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/baz'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#baz#'),
            ],
            50,
            false,
            ['dummy' => 'foo'],
            Tests\Fixtures\Classes\DummyLoggingCrawler::class,
            ['dummy' => 'foo'],
            Src\Crawler\Strategy\SortByLastModificationDateStrategy::getName(),
            Tests\Fixtures\Classes\DummyConfigurableParser::class,
            ['dummy' => 'foo'],
            Src\Formatter\JsonFormatter::getType(),
            'alerts.log',
            Log\LogLevel::ALERT,
            false,
            false,
            0,
        );

        $expected = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_fr.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/'),
                new Src\Sitemap\Url('https://www.example.com/baz'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#baz#'),
            ],
            50,
            true,
            [
                'foo' => 'baz',
                'dummy' => 'foo',
            ],
            Tests\Fixtures\Classes\DummyLoggingCrawler::class,
            [
                'foo' => 'baz',
                'dummy' => 'foo',
            ],
            Src\Crawler\Strategy\SortByLastModificationDateStrategy::getName(),
            Tests\Fixtures\Classes\DummyConfigurableParser::class,
            [
                'foo' => 'baz',
                'dummy' => 'foo',
            ],
            Src\Formatter\JsonFormatter::getType(),
            'alerts.log',
            Log\LogLevel::ALERT,
            true,
            true,
            300,
        );

        self::assertEquals($expected, $this->subject->merge($other));
    }

    #[Framework\Attributes\Test]
    public function toArrayReturnsAllConfigPropertiesWithDefaultAndCustomValues(): void
    {
        $subject = new Src\Config\CacheWarmupConfig(limit: 10);

        $expected = [
            'sitemaps' => [],
            'urls' => [],
            'excludePatterns' => [],
            'limit' => 10,
            'progress' => false,
            'clientOptions' => [],
            'crawler' => null,
            'crawlerOptions' => [],
            'strategy' => null,
            'parser' => null,
            'parserOptions' => [],
            'format' => Src\Formatter\TextFormatter::getType(),
            'logFile' => null,
            'logLevel' => Log\LogLevel::ERROR,
            'allowFailures' => false,
            'stopOnFailure' => false,
            'repeatAfter' => 0,
        ];

        self::assertSame($expected, $subject->toArray());
    }

    #[Framework\Attributes\Test]
    public function toArrayReturnsAllConfigPropertiesAndOmitsDefaultValues(): void
    {
        $subject = new Src\Config\CacheWarmupConfig(limit: 10);

        $expected = [
            'limit' => 10,
        ];

        self::assertSame($expected, $subject->toArray(true));
    }

    /**
     * @return Generator<string, array{Src\Sitemap\Sitemap|string, list<Src\Sitemap\Sitemap>}>
     */
    public static function addSitemapAddsGivenSitemapToSitemapsDataProvider(): Generator
    {
        $existingSitemap = Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml');
        $newSitemap = Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml');

        yield 'sitemap object' => [$newSitemap, [$existingSitemap, $newSitemap]];
        yield 'sitemap url' => ['https://www.example.com/sitemap_en.xml', [$existingSitemap, $newSitemap]];
    }

    /**
     * @return Generator<string, array{Src\Sitemap\Url|string, list<Src\Sitemap\Url>}>
     */
    public static function addUrlAddsGivenUrlToUrlsDataProvider(): Generator
    {
        $existingUrl = new Src\Sitemap\Url('https://www.example.com/');
        $newUrl = new Src\Sitemap\Url('https://www.example.com/foo');

        yield 'url object' => [$newUrl, [$existingUrl, $newUrl]];
        yield 'url string' => ['https://www.example.com/foo', [$existingUrl, $newUrl]];
    }
}
