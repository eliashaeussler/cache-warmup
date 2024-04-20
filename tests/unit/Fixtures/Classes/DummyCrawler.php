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

namespace EliasHaeussler\CacheWarmup\Tests\Fixtures\Classes;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Result;
use Psr\Http\Message;

use function array_shift;

/**
 * DummyCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
class DummyCrawler implements Crawler\Crawler
{
    /**
     * @var list<Message\UriInterface>
     */
    public static array $crawledUrls = [];

    /**
     * @var list<Result\CrawlingState>
     */
    public static array $resultStack = [];

    public function crawl(array $urls): Result\CacheWarmupResult
    {
        static::$crawledUrls = $urls;

        $result = new Result\CacheWarmupResult();
        $crawlingState = array_shift(static::$resultStack) ?? Result\CrawlingState::Successful;
        $urls = $this->mapUrlsToCrawlingResults(static::$crawledUrls, $crawlingState);

        foreach ($urls as $crawlingResult) {
            $result->addResult($crawlingResult);
        }

        return $result;
    }

    /**
     * @param list<Message\UriInterface> $urls
     *
     * @return list<Result\CrawlingResult>
     */
    private function mapUrlsToCrawlingResults(array $urls, Result\CrawlingState $state): array
    {
        return array_map(fn (Message\UriInterface $uri): Result\CrawlingResult => match ($state) {
            Result\CrawlingState::Failed => Result\CrawlingResult::createFailed($uri),
            default => Result\CrawlingResult::createSuccessful($uri),
        }, $urls);
    }
}
