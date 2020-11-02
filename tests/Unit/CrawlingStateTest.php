<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Tests\Unit;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\CrawlingState;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * CrawlingStateTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class CrawlingStateTest extends TestCase
{
    /**
     * @test
     */
    public function constructorThrowsExceptionIfInvalidCrawlingStateIsGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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

        static::assertSame($uri, $subject->getUri());
        static::assertSame($data, $subject->getData());
        static::assertTrue($subject->isSuccessful());
        static::assertTrue($subject->is(CrawlingState::SUCCESSFUL));
        static::assertFalse($subject->isFailed());
        static::assertFalse($subject->is(CrawlingState::FAILED));
    }

    /**
     * @test
     */
    public function createFailedReturnsFailedCrawlingStateForGivenUri(): void
    {
        $uri = new Uri('https://www.example.org/');
        $data = ['foo' => 'baz'];
        $subject = CrawlingState::createFailed($uri, $data);

        static::assertSame($uri, $subject->getUri());
        static::assertSame($data, $subject->getData());
        static::assertTrue($subject->isFailed());
        static::assertTrue($subject->is(CrawlingState::FAILED));
        static::assertFalse($subject->isSuccessful());
        static::assertFalse($subject->is(CrawlingState::SUCCESSFUL));
    }
}
