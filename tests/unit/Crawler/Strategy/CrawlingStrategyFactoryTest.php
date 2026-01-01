<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler\Strategy;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;

/**
 * CrawlingStrategyFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\Strategy\CrawlingStrategyFactory::class)]
final class CrawlingStrategyFactoryTest extends Framework\TestCase
{
    private Src\Crawler\Strategy\CrawlingStrategyFactory $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Crawler\Strategy\CrawlingStrategyFactory();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenStrategyDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\CrawlingStrategyDoesNotExist('foo'),
        );

        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getReturnsGivenCrawlingStrategyDataProvider')]
    public function getReturnsGivenCrawlingStrategy(string $name, Src\Crawler\Strategy\CrawlingStrategy $expected): void
    {
        self::assertEquals($expected, $this->subject->get($name));
    }

    #[Framework\Attributes\Test]
    public function getAllReturnsAllAvailableStrategies(): void
    {
        $expected = [
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            Src\Crawler\Strategy\SortByLastModificationDateStrategy::getName(),
            Src\Crawler\Strategy\SortByPriorityStrategy::getName(),
        ];

        self::assertSame($expected, $this->subject->getAll());
    }

    #[Framework\Attributes\Test]
    public function hasReturnsTrueIfGivenCrawlingStrategyExists(): void
    {
        self::assertTrue($this->subject->has(Src\Crawler\Strategy\SortByPriorityStrategy::getName()));
    }

    #[Framework\Attributes\Test]
    public function hasReturnsFalseIfGivenCrawlingStrategyDoesNotExists(): void
    {
        self::assertFalse($this->subject->has('foo'));
    }

    /**
     * @return Generator<string, array{string, Src\Crawler\Strategy\CrawlingStrategy}>
     */
    public static function getReturnsGivenCrawlingStrategyDataProvider(): Generator
    {
        yield 'sort by changefreq' => [
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            new Src\Crawler\Strategy\SortByChangeFrequencyStrategy(),
        ];
        yield 'sort by lastmod' => [
            Src\Crawler\Strategy\SortByLastModificationDateStrategy::getName(),
            new Src\Crawler\Strategy\SortByLastModificationDateStrategy(),
        ];
        yield 'sort by priority' => [
            Src\Crawler\Strategy\SortByPriorityStrategy::getName(),
            new Src\Crawler\Strategy\SortByPriorityStrategy(),
        ];
    }
}
