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

namespace EliasHaeussler\CacheWarmup\Event;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;

/**
 * CrawlingFinished.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class CrawlingFinished
{
    /**
     * @param list<Sitemap\Url> $urls
     */
    public function __construct(
        private array $urls,
        private Crawler\Crawler $crawler,
        private Result\CacheWarmupResult $result,
    ) {}

    /**
     * @return list<Sitemap\Url>
     */
    public function urls(): array
    {
        return $this->urls;
    }

    public function crawler(): Crawler\Crawler
    {
        return $this->crawler;
    }

    public function result(): Result\CacheWarmupResult
    {
        return $this->result;
    }
}
