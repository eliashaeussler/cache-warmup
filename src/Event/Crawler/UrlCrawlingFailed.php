<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Event\Crawler;

use EliasHaeussler\CacheWarmup\Result;
use Psr\Http\Message;
use Throwable;

/**
 * UrlCrawlingFailed.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UrlCrawlingFailed
{
    public function __construct(
        private readonly Message\UriInterface $uri,
        private readonly Throwable $exception,
        private Result\CrawlingResult $result,
    ) {}

    public function uri(): Message\UriInterface
    {
        return $this->uri;
    }

    public function exception(): Throwable
    {
        return $this->exception;
    }

    public function result(): Result\CrawlingResult
    {
        return $this->result;
    }

    public function setResult(Result\CrawlingResult $result): self
    {
        $this->result = $result;

        return $this;
    }
}
