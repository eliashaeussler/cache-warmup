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

namespace EliasHaeussler\CacheWarmup\Tests\Helper;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;

/**
 * StringHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\StringHelper::class)]
final class StringHelperTest extends Framework\TestCase
{
    /**
     * @return Generator<string, array{int, string}>
     */
    public static function formatNumberReturnsFormattedNumberDataProvider(): Generator
    {
        yield 'small number' => [123, '123'];
        yield 'large number' => [123 * 456 * 789, '44,253,432'];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('formatNumberReturnsFormattedNumberDataProvider')]
    public function formatNumberReturnsFormattedNumber(int $number, string $expected): void
    {
        self::assertSame($expected, Src\Helper\StringHelper::formatNumber($number));
    }
}
