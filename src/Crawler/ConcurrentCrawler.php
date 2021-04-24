<?php

declare(strict_types=1);

namespace EliasHaeussler\CacheWarmup\Crawler;

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

use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\CacheWarmup\Http\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * ConcurrentCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ConcurrentCrawler implements CrawlerInterface
{
    /**
     * @var UriInterface[]
     */
    protected $urls;

    /**
     * @var UriInterface[]
     */
    protected $successfulUrls = [];

    /**
     * @var UriInterface[]
     */
    protected $failedUrls = [];

    public function crawl(array $urls): void
    {
        $this->urls = array_values($urls);
        $this->successfulUrls = [];
        $this->failedUrls = [];

        // Create request pool
        $client = new Client();
        $pool = new Pool($client, $this->getRequests(), [
            'concurrency' => 5,
            'fulfilled' => [$this, 'onSuccess'],
            'rejected' => [$this, 'onFailure'],
        ]);

        // Start crawling
        $promise = $pool->promise();
        $promise->wait();
    }

    public function onSuccess(ResponseInterface $response, int $index): void
    {
        $data = [
            'response' => $response,
        ];
        $this->successfulUrls[] = CrawlingState::createSuccessful($this->urls[$index], $data);
    }

    public function onFailure(\Throwable $exception, int $index): void
    {
        $data = [
            'exception' => $exception,
        ];
        $this->failedUrls[] = CrawlingState::createFailed($this->urls[$index], $data);
    }

    protected function getRequests(): \Iterator
    {
        foreach ($this->urls as $url) {
            yield new Request('HEAD', $url);
        }
    }

    /**
     * @return CrawlingState[]
     */
    public function getSuccessfulUrls(): array
    {
        return $this->successfulUrls;
    }

    /**
     * @return CrawlingState[]
     */
    public function getFailedUrls(): array
    {
        return $this->failedUrls;
    }
}
