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

namespace EliasHaeussler\CacheWarmup\Tests\Exception;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use PHPUnit\Framework;

use function sprintf;

/**
 * InvalidCrawlerOptionExceptionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidCrawlerOptionException::class)]
final class InvalidCrawlerOptionExceptionTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function createReturnsExceptionForGivenCrawlerAndOption(): void
    {
        $crawler = new Tests\Fixtures\Classes\DummyConfigurableCrawler();
        $actual = Src\Exception\InvalidCrawlerOptionException::create($crawler, 'foo');

        self::assertSame(1659120894, $actual->getCode());
        self::assertSame(
            'The crawler option "foo" is invalid or not supported by crawler "'.$crawler::class.'".',
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function createForAllReturnsExceptionForGivenCrawlerAndOptionIfOnlyOneOptionIsGiven(): void
    {
        $crawler = new Tests\Fixtures\Classes\DummyConfigurableCrawler();
        $actual = Src\Exception\InvalidCrawlerOptionException::createForAll($crawler, ['foo']);

        self::assertSame(1659120894, $actual->getCode());
        self::assertSame(
            'The crawler option "foo" is invalid or not supported by crawler "'.$crawler::class.'".',
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function createForAllReturnsExceptionForGivenCrawlerAndOptions(): void
    {
        $crawler = new Tests\Fixtures\Classes\DummyConfigurableCrawler();

        $actual = Src\Exception\InvalidCrawlerOptionException::createForAll($crawler, ['foo', 'bar', 'baz']);

        self::assertSame(1659206995, $actual->getCode());
        self::assertSame(
            sprintf(
                'The crawler options "foo", "bar" and "baz" are invalid or not supported by crawler "%s".',
                $crawler::class,
            ),
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function forInvalidTypeReturnsExceptionForGivenOptions(): void
    {
        $actual = Src\Exception\InvalidCrawlerOptionException::forInvalidType(null);

        self::assertSame(1677424305, $actual->getCode());
        self::assertSame('The crawler options must be an associative array, null given.', $actual->getMessage());
    }
}
