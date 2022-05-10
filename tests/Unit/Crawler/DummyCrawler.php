<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\CacheWarmup\CrawlingState;
use Psr\Http\Message\UriInterface;

/**
 * DummyCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
class DummyCrawler implements CrawlerInterface
{
    /**
     * @var list<UriInterface>
     */
    public static $crawledUrls = [];

    /**
     * @var bool
     */
    public static $simulateFailure = false;

    public function crawl(array $urls): void
    {
        static::$crawledUrls = $urls;
    }

    public function getSuccessfulUrls(): array
    {
        if (!static::$simulateFailure) {
            return $this->mapUrlsToCrawlingStates(static::$crawledUrls, CrawlingState::SUCCESSFUL);
        }

        return [];
    }

    public function getFailedUrls(): array
    {
        if (static::$simulateFailure) {
            return $this->mapUrlsToCrawlingStates(static::$crawledUrls, CrawlingState::FAILED);
        }

        return [];
    }

    /**
     * @param list<UriInterface> $urls
     *
     * @return list<CrawlingState>
     */
    protected function mapUrlsToCrawlingStates(array $urls, int $state): array
    {
        return array_map(function (UriInterface $uri) use ($state): CrawlingState {
            switch ($state) {
                case CrawlingState::FAILED:
                    return CrawlingState::createFailed($uri);
                default:
                    return CrawlingState::createSuccessful($uri);
            }
        }, $urls);
    }
}
