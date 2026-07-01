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

use Closure;

use function max;

/**
 * ScopeProfiler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class ScopeProfiler
{
    /**
     * @var list<MeasurementSpan>
     */
    private static array $stack = [];

    private function __construct(
        private readonly ?string $action,
        private readonly MeasurementPoint $startPoint,
    ) {}

    public static function start(?string $action = null): self
    {
        return new self($action, MeasurementPoint::now());
    }

    /**
     * @template T
     *
     * @param Closure(): T $task
     *
     * @param-out T $result
     */
    public static function startAndExecute(string $action, Closure $task, mixed &$result = null): MeasurementSpan
    {
        $profiler = self::start($action);
        $result = (static fn () => $task())();

        return $profiler->stop();
    }

    public function stop(): MeasurementSpan
    {
        $endPoint = MeasurementPoint::now();
        $span = new MeasurementSpan(
            $this->action,
            $endPoint->time - $this->startPoint->time,
            $endPoint->memoryUsage - $this->startPoint->memoryUsage,
            max($endPoint->memoryPeak, $this->startPoint->memoryPeak),
        );

        self::$stack[] = $span;

        return $span;
    }

    /**
     * @return list<MeasurementSpan>
     */
    public static function releaseStack(): array
    {
        $stack = self::$stack;
        self::$stack = [];

        return $stack;
    }
}
