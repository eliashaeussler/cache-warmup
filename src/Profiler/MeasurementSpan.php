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

use EliasHaeussler\CacheWarmup\Helper;
use Stringable;

use function sprintf;

/**
 * Scope.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final readonly class MeasurementSpan implements Stringable
{
    public function __construct(
        public ?string $action,
        public float $duration,
        public int $memoryUsage,
        public int $memoryPeak,
    ) {}

    public function format(): string
    {
        $duration = $this->formatDuration();
        $memoryUsage = $this->formatMemoryUsage();
        $memoryPeak = $this->formatMemoryPeak();

        if ('' !== (string) $this->action) {
            return sprintf(
                '%s took %s and consumed %s of memory (peak at %s).',
                $this->action,
                $duration,
                $memoryUsage,
                $memoryPeak,
            );
        }

        return sprintf(
            'Finished after %s, consumed %s of memory (peak at %s).',
            $duration,
            $memoryUsage,
            $memoryPeak,
        );
    }

    public function formatDuration(): string
    {
        return Helper\StringHelper::formatDuration((int) $this->duration);
    }

    public function formatMemoryUsage(): string
    {
        return Helper\StringHelper::formatBytes($this->memoryUsage);
    }

    public function formatMemoryPeak(): string
    {
        return Helper\StringHelper::formatBytes($this->memoryPeak);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
