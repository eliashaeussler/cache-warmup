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

use EliasHaeussler\CacheWarmup\CacheWarmer;
use Generator;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

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
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $method,
        private readonly array $headers = [],
    ) {}

    public function build(Message\UriInterface $url): Message\RequestInterface
    {
        return new Psr7\Request(
            $this->method,
            $url,
            $this->buildHeaders(),
        );
    }

    /**
     * @param list<Message\UriInterface> $urls
     *
     * @return Generator<int, Message\RequestInterface>
     */
    public function buildIterable(array $urls): Generator
    {
        foreach ($urls as $url) {
            yield $this->build($url);
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(): array
    {
        $userAgent = sprintf(
            'EliasHaeussler-CacheWarmup/%s (https://github.com/eliashaeussler/cache-warmup)',
            CacheWarmer::VERSION,
        );

        return [
            'User-Agent' => $userAgent,
            ...$this->headers,
        ];
    }
}
