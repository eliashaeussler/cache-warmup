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

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use EliasHaeussler\CacheWarmup\Log;
use Psr\Http\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * LogHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class LogHandler implements ResponseHandler
{
    /**
     * @phpstan-param LogLevel::* $logLevel
     */
    public function __construct(
        private LoggerInterface $logger,
        private string $logLevel = LogLevel::ERROR,
    ) {}

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        if (Log\LogLevel::satisfies($this->logLevel, LogLevel::INFO)) {
            $this->logger->info(
                'URL {url} was successfully crawled (status code: {status_code}).',
                [
                    'url' => $uri,
                    'status_code' => $response->getStatusCode(),
                ],
            );
        }
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        if (Log\LogLevel::satisfies($this->logLevel, LogLevel::ERROR)) {
            $this->logger->error(
                'Error while crawling URL {url} (exception: {exception}).',
                [
                    'url' => $uri,
                    'exception' => $exception,
                ],
            );
        }
    }
}
