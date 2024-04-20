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

namespace EliasHaeussler\CacheWarmup\Tests\Result;

use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * CrawlingResultTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Result\CrawlingResult::class)]
final class CrawlingResultTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function createSuccessfulReturnsSuccessfulCrawlingStateForGivenUri(): void
    {
        $uri = new Psr7\Uri('https://www.example.org/');
        $data = ['foo' => 'baz'];
        $subject = Src\Result\CrawlingResult::createSuccessful($uri, $data);

        self::assertSame($uri, $subject->getUri());
        self::assertSame($data, $subject->getData());
        self::assertTrue($subject->isSuccessful());
        self::assertTrue($subject->is(Src\Result\CrawlingState::Successful));
        self::assertFalse($subject->isFailed());
        self::assertFalse($subject->is(Src\Result\CrawlingState::Failed));
    }

    #[Framework\Attributes\Test]
    public function createFailedReturnsFailedCrawlingStateForGivenUri(): void
    {
        $uri = new Psr7\Uri('https://www.example.org/');
        $data = ['foo' => 'baz'];
        $subject = Src\Result\CrawlingResult::createFailed($uri, $data);

        self::assertSame($uri, $subject->getUri());
        self::assertSame($data, $subject->getData());
        self::assertTrue($subject->isFailed());
        self::assertTrue($subject->is(Src\Result\CrawlingState::Failed));
        self::assertFalse($subject->isSuccessful());
        self::assertFalse($subject->is(Src\Result\CrawlingState::Successful));
    }

    #[Framework\Attributes\Test]
    public function stringRepresentationReturnsUri(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $subject = Src\Result\CrawlingResult::createSuccessful($uri);

        self::assertSame((string) $uri, (string) $subject);
    }
}
