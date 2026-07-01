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

namespace EliasHaeussler\CacheWarmup\Helper;

use NumberFormatter;
use Symfony\Component\Console;

/**
 * StringHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class StringHelper
{
    public static function formatBytes(int $bytes): string
    {
        return Console\Helper\Helper::formatMemory($bytes);
    }

    public static function formatDuration(int $milliseconds): string
    {
        return Console\Helper\Helper::formatTime($milliseconds / 1000);
    }

    public static function formatNumber(int $number): string
    {
        $formatted = NumberFormatter::create('en', NumberFormatter::DEFAULT_STYLE)->format($number);

        if (false === $formatted) {
            return (string) $number;
        }

        return $formatted;
    }
}
