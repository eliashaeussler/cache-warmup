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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message\Handler;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Exception;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Log;

/**
 * LogHandlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Handler\LogHandler::class)]
final class LogHandlerTest extends Framework\TestCase
{
    private Tests\Log\DummyLogger $logger;
    private Src\Http\Message\Handler\LogHandler $subject;

    protected function setUp(): void
    {
        $this->logger = new Tests\Log\DummyLogger();
        $this->subject = new Src\Http\Message\Handler\LogHandler($this->logger);
    }

    #[Framework\Attributes\Test]
    public function onSuccessDoesNothingIfConfiguredLogLevelDoesNotSatisfyInfoLevel(): void
    {
        $this->subject->onSuccess(
            new Psr7\Response(),
            new Psr7\Uri('https://www.example.com'),
        );

        self::assertSame([], $this->logger->log[Log\LogLevel::INFO]);
    }

    #[Framework\Attributes\Test]
    public function onSuccessLogsSuccessfulCrawl(): void
    {
        $expected = [
            [
                'message' => 'URL {url} was successfully crawled (status code: {status_code}).',
                'context' => [
                    'url' => new Psr7\Uri('https://www.example.com'),
                    'status_code' => 200,
                ],
            ],
        ];

        $subject = new Src\Http\Message\Handler\LogHandler($this->logger, Log\LogLevel::INFO);

        $subject->onSuccess(
            new Psr7\Response(),
            new Psr7\Uri('https://www.example.com'),
        );

        self::assertEquals($expected, $this->logger->log[Log\LogLevel::INFO]);
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNothingIfConfiguredLogLevelDoesNotSatisfyErrorLevel(): void
    {
        $subject = new Src\Http\Message\Handler\LogHandler($this->logger, Log\LogLevel::EMERGENCY);

        $subject->onFailure(
            new Exception(),
            new Psr7\Uri('https://www.example.com'),
        );

        self::assertSame([], $this->logger->log[Log\LogLevel::INFO]);
    }

    #[Framework\Attributes\Test]
    public function onFailureLogsFailedCrawl(): void
    {
        $expected = [
            [
                'message' => 'Error while crawling URL {url} (exception: {exception}).',
                'context' => [
                    'url' => new Psr7\Uri('https://www.example.com'),
                    'exception' => 'oops, something went wrong.',
                ],
            ],
        ];

        $this->subject->onFailure(
            new Exception('oops, something went wrong.'),
            new Psr7\Uri('https://www.example.com'),
        );

        self::assertEquals($expected, $this->logger->log[Log\LogLevel::ERROR]);
    }
}
