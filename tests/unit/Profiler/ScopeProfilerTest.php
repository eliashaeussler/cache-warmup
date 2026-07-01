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
use PHPUnit\Framework;

use function sleep;

/**
 * ScopeProfilerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Profiler\ScopeProfiler::class)]
final class ScopeProfilerTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function startAndExecuteMeasuresScope(): void
    {
        $function = function () {
            sleep(2);

            return 'foo';
        };

        $actual = Src\Profiler\ScopeProfiler::startAndExecute('foo', $function, $result);

        self::assertSame('foo', $result);
        self::assertGreaterThan(2000, $actual->duration);
    }

    #[Framework\Attributes\Test]
    public function stopReturnsMeasuredScope(): void
    {
        $subject = Src\Profiler\ScopeProfiler::start('foo');

        sleep(1);

        $actual = $subject->stop();

        self::assertSame('foo', $actual->action);
        self::assertGreaterThan(0, $actual->duration);
    }
}
