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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Time;

use EliasHaeussler\CacheWarmup\Time;
use Generator;
use PHPUnit\Framework;

/**
 * DurationTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DurationTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function getReturnsDurationInMilliseconds(): void
    {
        $subject = new Time\Duration(123.45);

        self::assertSame(123.45, $subject->get());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('formatReturnsFormattedDurationDataProvider')]
    public function formatReturnsFormattedDuration(float $milliseconds, string $expected): void
    {
        $subject = new Time\Duration($milliseconds);

        self::assertSame($expected, $subject->format());
    }

    /**
     * @return Generator<string, array{float, string}>
     */
    public static function formatReturnsFormattedDurationDataProvider(): Generator
    {
        yield 'milliseconds' => [1.23456, '1.235ms'];
        yield 'seconds' => [1234.56, '1.235s'];
    }
}
