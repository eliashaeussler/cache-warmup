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

namespace EliasHaeussler\CacheWarmup\Result;

use Psr\Http\Message;
use Stringable;

/**
 * CrawlingResult.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class CrawlingResult implements Stringable
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        private Message\UriInterface $uri,
        private CrawlingState $state,
        private array $data = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function createSuccessful(Message\UriInterface $uri, array $data = []): self
    {
        return new self($uri, CrawlingState::Successful, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFailed(Message\UriInterface $uri, array $data = []): self
    {
        return new self($uri, CrawlingState::Failed, $data);
    }

    public function getUri(): Message\UriInterface
    {
        return $this->uri;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return $this->is(CrawlingState::Successful);
    }

    public function isFailed(): bool
    {
        return $this->is(CrawlingState::Failed);
    }

    public function is(CrawlingState $state): bool
    {
        return $this->state === $state;
    }

    public function __toString(): string
    {
        return (string) $this->uri;
    }
}
