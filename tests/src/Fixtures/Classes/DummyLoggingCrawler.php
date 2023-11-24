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

namespace EliasHaeussler\CacheWarmup\Tests\Fixtures\Classes;

use EliasHaeussler\CacheWarmup\Crawler;
use Psr\Log;

/**
 * DummyLoggingCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class DummyLoggingCrawler extends DummyCrawler implements Crawler\LoggingCrawlerInterface
{
    public static ?Log\LoggerInterface $logger = null;

    /**
     * @phpstan-var Log\LogLevel::*|null
     */
    public static ?string $logLevel = null;

    public function setLogger(Log\LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        self::$logLevel = $logLevel;
    }
}
