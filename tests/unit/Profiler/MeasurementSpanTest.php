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

namespace EliasHaeussler\CacheWarmup\Tests\Profiler;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;

/**
 * MeasurementSpanTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Profiler\MeasurementSpan::class)]
final class MeasurementSpanTest extends Framework\TestCase
{
    /**
     * @return Generator<string, array{string|null, string}>
     */
    public static function formatReturnsFormattedMeasurementDataProvider(): Generator
    {
        yield 'without action' => [null, 'Finished after 123 ms, consumed 678 B of memory (peak at 901 B).'];
        yield 'with action' => ['Foo', 'Foo took 123 ms and consumed 678 B of memory (peak at 901 B).'];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('formatReturnsFormattedMeasurementDataProvider')]
    public function formatReturnsFormattedMeasurement(?string $action, string $expected): void
    {
        $subject = new Src\Profiler\MeasurementSpan($action, 123.45, 678, 901);

        self::assertSame($expected, $subject->format());
    }

    /**
     * @return Generator<string, array{float, string}>
     */
    public static function formatDurationReturnsFormattedDurationDataProvider(): Generator
    {
        yield 'milliseconds' => [1.23456, '1 ms'];
        yield 'seconds' => [1234.56, '1 s'];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('formatDurationReturnsFormattedDurationDataProvider')]
    public function formatDurationReturnsFormattedDuration(float $duration, string $expected): void
    {
        $subject = new Src\Profiler\MeasurementSpan(null, $duration, 0, 0);

        self::assertSame($expected, $subject->formatDuration());
    }

    #[Framework\Attributes\Test]
    public function formatMemoryUsageReturnsFormattedMemoryUsage(): void
    {
        $subject = new Src\Profiler\MeasurementSpan(null, 0, 678, 901);

        self::assertSame('678 B', $subject->formatMemoryUsage());
    }

    #[Framework\Attributes\Test]
    public function subjectIsStringable(): void
    {
        $subject = new Src\Profiler\MeasurementSpan('Foo', 123.45, 678, 901);

        self::assertSame('Foo took 123 ms and consumed 678 B of memory (peak at 901 B).', (string) $subject);
    }
}
