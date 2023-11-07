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

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Http;
use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function array_values;
use function sprintf;

/**
 * ConcurrentCrawlerTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait ConcurrentCrawlerTrait
{
    /**
     * @param list<Message\UriInterface>                          $urls
     * @param list<Http\Message\Handler\ResponseHandlerInterface> $handlers
     */
    protected function createPool(
        array $urls,
        ClientInterface $client,
        array $handlers = [],
        bool $stopOnFailure = false,
    ): Pool {
        return Http\Message\RequestPoolFactory::create($this->buildRequests($urls))
            ->withClient($client)
            ->withConcurrency($this->options['concurrency'])
            ->withOptions($this->options['request_options'])
            ->withResponseHandler(...array_values($handlers))
            ->withStopOnFailure($stopOnFailure)
            ->createPool()
        ;
    }

    /**
     * @param list<Message\UriInterface> $urls
     *
     * @return Generator<int, Message\RequestInterface>
     */
    protected function buildRequests(array $urls): Generator
    {
        foreach ($urls as $url) {
            yield new Psr7\Request(
                $this->options['request_method'],
                $url,
                $this->getRequestHeaders(),
            );
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getRequestHeaders(): array
    {
        $currentVersion = Helper\VersionHelper::getCurrentVersion() ?? '1.0';
        $userAgent = sprintf('EliasHaeussler-CacheWarmup/%s (https://github.com/eliashaeussler/cache-warmup)', $currentVersion);

        return [
            'User-Agent' => $userAgent,
            ...$this->options['request_headers'],
        ];
    }
}
