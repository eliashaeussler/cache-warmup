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

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Result;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7;
use Psr\Http\Message;
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
 *     request_headers: array<string, string>
 * }>
 */
class ConcurrentCrawler extends AbstractConfigurableCrawler
{
    protected static array $defaultOptions = [
        'concurrency' => 5,
        'request_method' => 'HEAD',
        'request_headers' => [],
    ];

    public function __construct(
        array $options = [],
        protected readonly ClientInterface $client = new Client(),
    ) {
        parent::__construct($options);
    }

    public function crawl(array $urls): Result\CacheWarmupResult
    {
        $result = new Result\CacheWarmupResult();
        $urls = array_values($urls);

        // Create request pool
        $pool = new Pool($this->client, $this->buildRequests($urls), [
            'concurrency' => $this->options['concurrency'],
            'fulfilled' => function (Message\ResponseInterface $response, int $index) use ($result, $urls) {
                $state = $this->onSuccess($response, $urls[$index]);
                $result->addResult($state);
            },
            'rejected' => function (Throwable $exception, int $index) use ($result, $urls) {
                $state = $this->onFailure($exception, $urls[$index]);
                $result->addResult($state);
            },
        ]);

        // Start crawling
        $promise = $pool->promise();
        $promise->wait();

        return $result;
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $url): Result\CrawlingResult
    {
        $data = [
            'response' => $response,
        ];

        return Result\CrawlingResult::createSuccessful($url, $data);
    }

    public function onFailure(Throwable $exception, Message\UriInterface $url): Result\CrawlingResult
    {
        $data = [
            'exception' => $exception,
        ];

        return Result\CrawlingResult::createFailed($url, $data);
    }

    /**
     * @param list<Message\UriInterface> $urls
     *
     * @return Generator<Message\RequestInterface>
     */
    protected function buildRequests(array $urls): Generator
    {
        foreach ($urls as $url) {
            yield new Psr7\Request($this->options['request_method'], $url, $this->options['request_headers']);
        }
    }
}
