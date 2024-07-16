<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\CrawlingState;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Iterator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * ConcurrentCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @extends AbstractConfigurableCrawler<array{
 *     concurrency: int,
 *     request_method: string,
 *     request_headers: array<string, string>,
 *     client_config: array<string, mixed>
 * }>
 */
class ConcurrentCrawler extends AbstractConfigurableCrawler
{
    protected static $defaultOptions = [
        'concurrency' => 5,
        'request_method' => 'HEAD',
        'request_headers' => [],
        'client_config' => [],
    ];

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var UriInterface[]
     */
    protected $urls;

    /**
     * @var CrawlingState[]
     */
    protected $successfulUrls = [];

    /**
     * @var CrawlingState[]
     */
    protected $failedUrls = [];

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->client = $this->initializeClient();
    }

    public function crawl(array $urls): void
    {
        $this->urls = array_values($urls);
        $this->successfulUrls = [];
        $this->failedUrls = [];

        // Create request pool
        $pool = new Pool($this->client, $this->getRequests(), [
            'concurrency' => $this->options['concurrency'],
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

    public function onFailure(Throwable $exception, int $index): void
    {
        $data = [
            'exception' => $exception,
        ];
        $this->failedUrls[] = CrawlingState::createFailed($this->urls[$index], $data);
    }

    /**
     * @return Iterator<Request>
     */
    protected function getRequests(): Iterator
    {
        foreach ($this->urls as $url) {
            yield new Request($this->options['request_method'], $url, $this->options['request_headers']);
        }
    }

    protected function initializeClient(): ClientInterface
    {
        return new Client($this->options['client_config']);
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
