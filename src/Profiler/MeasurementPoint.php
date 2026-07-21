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

namespace EliasHaeussler\CacheWarmup\Profiler;

use function memory_get_peak_usage;

/**
 * MeasurementPoint.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final readonly class MeasurementPoint
{
    public function __construct(
        public float $time,
        public int $memoryUsage,
        public int $memoryPeak,
    ) {}

    public static function now(): self
    {
        return new self(self::currentTime(), self::currentMemoryUsage(), self::currentMemoryPeak());
    }

    private static function currentTime(): float
    {
        return microtime(true) * 1000;
    }

    private static function currentMemoryUsage(): int
    {
        return memory_get_usage(true);
    }

    private static function currentMemoryPeak(): int
    {
        return memory_get_peak_usage(true);
    }
}
