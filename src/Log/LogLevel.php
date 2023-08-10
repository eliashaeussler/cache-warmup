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

namespace EliasHaeussler\CacheWarmup\Log;

use EliasHaeussler\CacheWarmup\Exception;
use Psr\Log;

use function strtolower;

/**
 * LogLevel.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
enum LogLevel: int
{
    case Emergency = 7;
    case Alert = 6;
    case Critical = 5;
    case Error = 4;
    case Warning = 3;
    case Notice = 2;
    case Info = 1;
    case Debug = 0;

    public function satisfies(self $other): bool
    {
        return $this->value <= $other->value;
    }

    /**
     * @throws Exception\UnsupportedLogLevelException
     */
    public static function fromName(string $level): self
    {
        return match (strtolower($level)) {
            'emergency' => self::Emergency,
            'alert' => self::Alert,
            'critical' => self::Critical,
            'error' => self::Error,
            'warning' => self::Warning,
            'notice' => self::Notice,
            'info' => self::Info,
            'debug' => self::Debug,
            default => throw Exception\UnsupportedLogLevelException::create($level),
        };
    }

    /**
     * @phpstan-param Log\LogLevel::* $psrLogLevel
     */
    public static function fromPsrLogLevel(string $psrLogLevel): self
    {
        return match ($psrLogLevel) {
            Log\LogLevel::EMERGENCY => self::Emergency,
            Log\LogLevel::ALERT => self::Alert,
            Log\LogLevel::CRITICAL => self::Critical,
            Log\LogLevel::ERROR => self::Error,
            Log\LogLevel::WARNING => self::Warning,
            Log\LogLevel::NOTICE => self::Notice,
            Log\LogLevel::INFO => self::Info,
            Log\LogLevel::DEBUG => self::Debug,
        };
    }
}
