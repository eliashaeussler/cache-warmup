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

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use EliasHaeussler\CacheWarmup\Event;
use EliasHaeussler\CacheWarmup\Result;
use Psr\EventDispatcher;
use Psr\Http\Message;
use Throwable;

/**
 * ResultCollectorHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ResultCollectorHandler implements ResponseHandler
{
    private readonly Result\CacheWarmupResult $result;

    public function __construct(
        private readonly ?EventDispatcher\EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->result = new Result\CacheWarmupResult();
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $result = Result\CrawlingResult::createSuccessful($uri, [
            'response' => $response,
        ]);

        $event = new Event\UrlCrawlingSucceeded($uri, $response, $result);
        $this->eventDispatcher?->dispatch($event);

        $this->result->addResult($event->result());
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        $result = Result\CrawlingResult::createFailed($uri, [
            'exception' => $exception,
        ]);

        $event = new Event\UrlCrawlingFailed($uri, $exception, $result);
        $this->eventDispatcher?->dispatch($event);

        $this->result->addResult($event->result());
    }

    public function getResult(): Result\CacheWarmupResult
    {
        return $this->result;
    }
}
