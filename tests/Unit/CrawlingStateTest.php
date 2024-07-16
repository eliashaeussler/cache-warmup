<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use EliasHaeussler\CacheWarmup\CrawlingState;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * CrawlingStateTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CrawlingStateTest extends TestCase
{
    /**
     * @test
     */
    public function constructorThrowsExceptionIfInvalidCrawlingStateIsGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1604334815);
        new CrawlingState(new Uri(''), -1);
    }

    /**
     * @test
     */
    public function createSuccessfulReturnsSuccessfulCrawlingStateForGivenUri(): void
    {
        $uri = new Uri('https://www.example.org/');
        $data = ['foo' => 'baz'];
        $subject = CrawlingState::createSuccessful($uri, $data);

        self::assertSame($uri, $subject->getUri());
        self::assertSame($data, $subject->getData());
        self::assertTrue($subject->isSuccessful());
        self::assertTrue($subject->is(CrawlingState::SUCCESSFUL));
        self::assertFalse($subject->isFailed());
        self::assertFalse($subject->is(CrawlingState::FAILED));
    }

    /**
     * @test
     */
    public function createFailedReturnsFailedCrawlingStateForGivenUri(): void
    {
        $uri = new Uri('https://www.example.org/');
        $data = ['foo' => 'baz'];
        $subject = CrawlingState::createFailed($uri, $data);

        self::assertSame($uri, $subject->getUri());
        self::assertSame($data, $subject->getData());
        self::assertTrue($subject->isFailed());
        self::assertTrue($subject->is(CrawlingState::FAILED));
        self::assertFalse($subject->isSuccessful());
        self::assertFalse($subject->is(CrawlingState::SUCCESSFUL));
    }
}
