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

namespace EliasHaeussler\CacheWarmup\Tests\Log;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;
use Psr\Log;

/**
 * LogLevelTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\LogLevel::class)]
final class LogLevelTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function satisfiesReturnsTrueIfLogLevelSatisfiesGivenLogLevel(): void
    {
        $level = Log\LogLevel::WARNING;

        self::assertTrue(Src\Log\LogLevel::satisfies($level, Log\LogLevel::ERROR));
        self::assertFalse(Src\Log\LogLevel::satisfies($level, Log\LogLevel::NOTICE));
    }

    #[Framework\Attributes\Test]
    public function getAllReturnsAllAvailableLogLevels(): void
    {
        $expected = [
            Log\LogLevel::EMERGENCY,
            Log\LogLevel::ALERT,
            Log\LogLevel::CRITICAL,
            Log\LogLevel::ERROR,
            Log\LogLevel::WARNING,
            Log\LogLevel::NOTICE,
            Log\LogLevel::INFO,
            Log\LogLevel::DEBUG,
        ];

        self::assertSame($expected, Src\Log\LogLevel::getAll());
    }
}
