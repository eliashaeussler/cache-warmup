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

namespace EliasHaeussler\CacheWarmup\Http\Message;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;
use Psr\Http\Message;
use Throwable;

use function array_values;

/**
 * RequestPoolFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class RequestPoolFactory
{
    private ClientInterface $client;
    private int $concurrency = 5;

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    /**
     * @var list<Handler\ResponseHandler>
     */
    private array $handlers = [];
    private bool $stopOnFailure = false;

    /**
     * @var array<int, Message\RequestInterface>
     */
    private array $visited = [];

    /**
     * @param iterable<int, Message\RequestInterface> $requests
     */
    private function __construct(
        private readonly iterable $requests,
    ) {
        $this->client = new Client();
    }

    /**
     * @param iterable<int, Message\RequestInterface> $requests
     */
    public static function create(iterable $requests): self
    {
        return new self($requests);
    }

    public function createPool(): Pool
    {
        return new Pool(
            $this->client,
            $this->decorateRequests(),
            [
                'concurrency' => $this->concurrency,
                'options' => $this->options,
                'fulfilled' => $this->onFulfilled(...),
                'rejected' => $this->onRejected(...),
            ],
        );
    }

    private function onFulfilled(Message\ResponseInterface $response, int $index): void
    {
        foreach ($this->handlers as $handler) {
            $handler->onSuccess($response, $this->visited[$index]->getUri());
        }
    }

    private function onRejected(Throwable $throwable, int $index, Promise\Promise $aggregate): void
    {
        foreach ($this->handlers as $handler) {
            $handler->onFailure($throwable, $this->visited[$index]->getUri());
        }

        if ($this->stopOnFailure) {
            $aggregate->cancel();
        }
    }

    /**
     * @return Generator<Message\RequestInterface>
     */
    private function decorateRequests(): Generator
    {
        foreach ($this->requests as $index => $request) {
            yield $this->visited[$index] = $request;
        }
    }

    public function withClient(ClientInterface $client): self
    {
        $clone = clone $this;
        $clone->client = $client;

        return $clone;
    }

    public function withConcurrency(int $concurrency): self
    {
        $clone = clone $this;
        $clone->concurrency = $concurrency;

        return $clone;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): self
    {
        $clone = clone $this;
        $clone->options = $options;

        return $clone;
    }

    public function withResponseHandler(Handler\ResponseHandler ...$handler): self
    {
        $clone = clone $this;
        $clone->handlers = [...$clone->handlers, ...array_values($handler)];

        return $clone;
    }

    public function withStopOnFailure(bool $stopOnFailure = true): self
    {
        $clone = clone $this;
        $clone->stopOnFailure = $stopOnFailure;

        return $clone;
    }
}
