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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Http\Message\Handler;

use EliasHaeussler\CacheWarmup as Src;
use Exception;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * ResultCollectorHandlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Handler\ResultCollectorHandler::class)]
final class ResultCollectorHandlerTest extends Framework\TestCase
{
    private Src\Http\Message\Handler\ResultCollectorHandler $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Http\Message\Handler\ResultCollectorHandler();
    }

    #[Framework\Attributes\Test]
    public function onSuccessAddsSuccessfulCrawlingResult(): void
    {
        $response = new Psr7\Response();
        $uri = new Psr7\Uri('https://www.example.com');

        $expected = Src\Result\CrawlingResult::createSuccessful($uri, ['response' => $response]);

        self::assertSame([], $this->subject->getResult()->getSuccessful());
        self::assertSame([], $this->subject->getResult()->getFailed());

        $this->subject->onSuccess($response, $uri);

        self::assertEquals([$expected], $this->subject->getResult()->getSuccessful());
        self::assertSame([], $this->subject->getResult()->getFailed());
    }

    #[Framework\Attributes\Test]
    public function onFailureAddsFailedCrawlingResult(): void
    {
        $exception = new Exception('foo');
        $uri = new Psr7\Uri('https://www.example.com');

        $expected = Src\Result\CrawlingResult::createFailed($uri, ['exception' => $exception]);

        self::assertSame([], $this->subject->getResult()->getSuccessful());
        self::assertSame([], $this->subject->getResult()->getFailed());

        $this->subject->onFailure($exception, $uri);

        self::assertSame([], $this->subject->getResult()->getSuccessful());
        self::assertEquals([$expected], $this->subject->getResult()->getFailed());
    }
}
