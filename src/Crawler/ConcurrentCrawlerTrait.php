<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Http;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message;
use Symfony\Component\OptionsResolver;

use function array_key_exists;

/**
 * ConcurrentCrawlerTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait ConcurrentCrawlerTrait
{
    protected function configureOptions(OptionsResolver\OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('concurrency')
            ->allowedTypes('int')
            ->required()
            ->default(5)
        ;

        $optionsResolver->define('request_method')
            ->allowedTypes('string')
            ->required()
            ->default('HEAD')
        ;

        $optionsResolver->define('request_headers')
            ->allowedTypes('array')
            ->default([])
        ;

        $optionsResolver->define('request_options')
            ->allowedTypes('array')
            ->default([])
        ;

        $optionsResolver->define('write_response_body')
            ->allowedTypes('bool')
            ->default(false)
        ;
    }

    /**
     * @param list<Message\UriInterface>                 $urls
     * @param list<Http\Message\Handler\ResponseHandler> $handlers
     */
    protected function createPool(
        array $urls,
        ClientInterface $client,
        array $handlers = [],
        bool $stopOnFailure = false,
    ): Pool {
        $requestFactory = $this->createRequestFactory();
        $options = $this->options['request_options'];

        if (!$this->options['write_response_body'] && !array_key_exists(RequestOptions::SINK, $options)) {
            $options[RequestOptions::SINK] = new Http\Message\Stream\NullStream();
        }

        return Http\Message\RequestPoolFactory::create($requestFactory->createRequests($urls))
            ->withClient($client)
            ->withConcurrency($this->options['concurrency'])
            ->withOptions($options)
            ->withResponseHandler(...$handlers)
            ->withStopOnFailure($stopOnFailure)
            ->createPool()
        ;
    }

    protected function createRequestFactory(): Http\Message\RequestFactory
    {
        return Http\Message\RequestFactory::create($this->options['request_method'])
            ->withHeaders($this->options['request_headers'])
            ->withUserAgent(true)
        ;
    }
}
