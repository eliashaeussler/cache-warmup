<?php

declare(strict_types=1);

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use GuzzleHttp\Psr7\Uri;

/**
 * DummyCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class DummyCrawler implements CrawlerInterface
{
    /**
     * @var Uri[]
     */
    public static $crawledUrls = [];

    public function crawl(array $urls): void
    {
        static::$crawledUrls = $urls;
    }

    public function getSuccessfulUrls(): array
    {
        return [];
    }

    public function getFailedUrls(): array
    {
        return [];
    }
}
