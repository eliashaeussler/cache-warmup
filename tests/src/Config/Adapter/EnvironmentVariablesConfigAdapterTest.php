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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Adapter;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Generator;
use PHPUnit\Framework;
use Psr\Log;

use function array_keys;
use function putenv;

/**
 * EnvironmentVariablesConfigAdapterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\EnvironmentVariablesConfigAdapter::class)]
final class EnvironmentVariablesConfigAdapterTest extends Framework\TestCase
{
    private Src\Config\Adapter\EnvironmentVariablesConfigAdapter $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Config\Adapter\EnvironmentVariablesConfigAdapter();
    }

    #[Framework\Attributes\Test]
    public function getSkipsNonExistingEnvironmentVariables(): void
    {
        $expected = new Src\Config\CacheWarmupConfig(limit: 10);

        $this->testWithEnvironment(
            fn () => self::assertEquals($expected, $this->subject->get()),
            [
                'CACHE_WARMUP_LIMIT' => '10',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredCrawlerOptionsAreInvalid(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerOptionException::forInvalidType('foo'));

        $this->testWithEnvironment(
            fn () => $this->subject->get(),
            [
                'CACHE_WARMUP_CRAWLER_OPTIONS' => 'foo',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredCrawlerIsInvalid(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerException::forMissingClass('foo'));

        $this->testWithEnvironment(
            fn () => $this->subject->get(),
            [
                'CACHE_WARMUP_CRAWLER' => 'foo',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getConvertsArrayValues(): void
    {
        $expected = new Src\Config\CacheWarmupConfig([
            Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
            Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
        ]);
        $this->testWithEnvironment(
            fn () => self::assertEquals($expected, $this->subject->get()),
            [
                'CACHE_WARMUP_SITEMAPS' => 'https://www.example.com/sitemap.xml,https://www.example.com/sitemap_en.xml',
            ],
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getRespectsVariousTypesOfBooleanValuesDataProvider')]
    public function getRespectsVariousTypesOfBooleanValues(string $value, bool $expected): void
    {
        $this->testWithEnvironment(
            fn () => self::assertSame($expected, $this->subject->get()->isProgressBarEnabled()),
            [
                'CACHE_WARMUP_PROGRESS' => $value,
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getMapsEnvironmentVariablesToCacheWarmupConfig(): void
    {
        $variables = [
            'CACHE_WARMUP_SITEMAPS' => 'https://www.example.com/sitemap.xml,https://www.example.com/sitemap_en.xml',
            'CACHE_WARMUP_URLS' => 'https://www.example.com/,https://www.example.com/foo',
            'CACHE_WARMUP_EXCLUDE_PATTERNS' => '#foo#,*foo*',
            'CACHE_WARMUP_LIMIT' => '10',
            'CACHE_WARMUP_PROGRESS' => 'true',
            'CACHE_WARMUP_CRAWLER' => Tests\Fixtures\Classes\DummyCrawler::class,
            'CACHE_WARMUP_CRAWLER_OPTIONS' => '{"foo":"baz"}',
            'CACHE_WARMUP_STRATEGY' => Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            'CACHE_WARMUP_FORMAT' => Src\Formatter\JsonFormatter::getType(),
            'CACHE_WARMUP_LOG_FILE' => 'errors.log',
            'CACHE_WARMUP_LOG_LEVEL' => Log\LogLevel::DEBUG,
            'CACHE_WARMUP_ALLOW_FAILURES' => 'true',
            'CACHE_WARMUP_STOP_ON_FAILURE' => 'true',
            'CACHE_WARMUP_REPEAT_AFTER' => '300',
        ];

        $expected = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/'),
                new Src\Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#'),
                Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
            ],
            10,
            true,
            Tests\Fixtures\Classes\DummyCrawler::class,
            ['foo' => 'baz'],
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            Src\Formatter\JsonFormatter::getType(),
            'errors.log',
            Log\LogLevel::DEBUG,
            true,
            true,
            300,
        );

        $this->testWithEnvironment(
            fn () => self::assertEquals($expected, $this->subject->get()),
            $variables,
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionOnInvalidEnvironmentVariables(): void
    {
        $this->expectException(Src\Exception\InvalidEnvironmentVariablesException::class);
        $this->expectExceptionCode(1708635629);

        $this->testWithEnvironment(
            fn () => $this->subject->get(),
            [
                // Must be a non-negative integer
                'CACHE_WARMUP_LIMIT' => 'foo',
            ],
        );
    }

    /**
     * @param array<string, string> $environmentVariables
     */
    private function testWithEnvironment(callable $test, array $environmentVariables): void
    {
        foreach ($environmentVariables as $name => $value) {
            putenv($name.'='.$value);
        }

        try {
            $test();
        } finally {
            foreach (array_keys($environmentVariables) as $name) {
                putenv($name);
            }
        }
    }

    /**
     * @return Generator<string, array{string, bool}>
     */
    public static function getRespectsVariousTypesOfBooleanValuesDataProvider(): Generator
    {
        yield 'true' => ['true', true];
        yield 'yes' => ['yes', true];
        yield '1' => ['1', true];
        yield 'false' => ['false', false];
        yield 'no' => ['no', false];
        yield '0' => ['0', false];
    }
}
