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

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\CacheWarmup\CrawlingState;
use Psr\Http\Message\UriInterface;

/**
 * CrawlerResultProcessorTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait CrawlerResultProcessorTrait
{
    /**
     * @return UriInterface[]
     */
    protected function getProcessedUrlsFromCrawler(CrawlerInterface $crawler, int $state = null): array
    {
        $urls = [];
        $crawledUrls = array_merge($crawler->getSuccessfulUrls(), $crawler->getFailedUrls());
        /** @var CrawlingState $crawlingState */
        foreach ($crawledUrls as $crawlingState) {
            if (null === $state || $crawlingState->is($state)) {
                $urls[] = $crawlingState->getUri();
            }
        }

        return $urls;
    }
}
