<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Log;

use Psr\Log;

/**
 * LogLevel.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class LogLevel extends Log\LogLevel
{
    private const VALUE_MAP = [
        Log\LogLevel::EMERGENCY => 7,
        Log\LogLevel::ALERT => 6,
        Log\LogLevel::CRITICAL => 5,
        Log\LogLevel::ERROR => 4,
        Log\LogLevel::WARNING => 3,
        Log\LogLevel::NOTICE => 2,
        Log\LogLevel::INFO => 1,
        Log\LogLevel::DEBUG => 0,
    ];

    /**
     * @phpstan-param Log\LogLevel::* $level
     * @phpstan-param Log\LogLevel::* $other
     */
    public static function satisfies(string $level, string $other): bool
    {
        return self::VALUE_MAP[$level] <= self::VALUE_MAP[$other];
    }

    /**
     * @return array<Log\LogLevel::*>
     */
    public static function getAll(): array
    {
        return [
            Log\LogLevel::EMERGENCY,
            Log\LogLevel::ALERT,
            Log\LogLevel::CRITICAL,
            Log\LogLevel::ERROR,
            Log\LogLevel::WARNING,
            Log\LogLevel::NOTICE,
            Log\LogLevel::INFO,
            Log\LogLevel::DEBUG,
        ];
    }
}
