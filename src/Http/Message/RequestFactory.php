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

namespace EliasHaeussler\CacheWarmup\Http\Message;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use Generator;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function array_key_exists;
use function sprintf;

/**
 * RequestFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class RequestFactory
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private function __construct(
        private readonly string $method,
    ) {}

    public static function create(string $method): self
    {
        return new self($method);
    }

    public function createRequest(Message\UriInterface $url): Message\RequestInterface
    {
        return new Psr7\Request($this->method, $url, $this->headers);
    }

    /**
     * @param list<Message\UriInterface> $urls
     *
     * @return Generator<int, Message\RequestInterface>
     */
    public function createRequests(array $urls): Generator
    {
        foreach ($urls as $url) {
            yield $this->createRequest($url);
        }
    }

    /**
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        $clone = clone $this;
        $clone->headers = $headers;

        return $clone;
    }

    public function withUserAgent(bool $skipIfAlreadyPresent = false): self
    {
        $clone = clone $this;

        if (!$skipIfAlreadyPresent || !array_key_exists('User-Agent', $clone->headers)) {
            $clone->headers['User-Agent'] = $this->createUserAgentHeader();
        }

        return $clone;
    }

    private function createUserAgentHeader(): string
    {
        return sprintf(
            'EliasHaeussler-CacheWarmup/%s (https://cache-warmup.dev)',
            CacheWarmer::VERSION,
        );
    }
}
