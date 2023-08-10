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

use EliasHaeussler\CacheWarmup as Src;
use Exception;
use PHPUnit\Framework;

use function sleep;

/**
 * TimeTrackerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Time\TimeTracker::class)]
final class TimeTrackerTest extends Framework\TestCase
{
    private Src\Time\TimeTracker $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Time\TimeTracker();
    }

    #[Framework\Attributes\Test]
    public function trackTracksTimeAndReturnsResultFromFunction(): void
    {
        $function = function () {
            sleep(2);

            return 'foo';
        };

        self::assertNull($this->subject->getLastDuration());
        self::assertSame('foo', $this->subject->track($function));
        self::assertNotNull($this->subject->getLastDuration());
        self::assertGreaterThan(2000, $this->subject->getLastDuration()->get());
    }

    #[Framework\Attributes\Test]
    public function trackStoresDurationIfFunctionIsErroneous(): void
    {
        $function = static fn () => throw new Exception('dummy');

        self::assertNull($this->subject->getLastDuration());

        try {
            $this->subject->track($function);
        } catch (Exception) {
            // Intended fallthrough.
        }

        self::assertNotNull($this->subject->getLastDuration());
    }
}
