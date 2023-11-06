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

namespace EliasHaeussler\CacheWarmup\Tests\Result;

use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * CacheWarmupResultTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Result\CacheWarmupResult::class)]
final class CacheWarmupResultTest extends Framework\TestCase
{
    private Src\Result\CacheWarmupResult $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Result\CacheWarmupResult();
    }

    #[Framework\Attributes\Test]
    public function addResultAddsSuccessfulResult(): void
    {
        $result = Src\Result\CrawlingResult::createSuccessful(new Psr7\Uri('https://www.example.com'));

        self::assertSame([], $this->subject->getSuccessful());

        $this->subject->addResult($result);

        self::assertSame([$result], $this->subject->getSuccessful());
    }

    #[Framework\Attributes\Test]
    public function addResultAddsFailedResult(): void
    {
        $result = Src\Result\CrawlingResult::createFailed(new Psr7\Uri('https://www.example.com'));

        self::assertSame([], $this->subject->getFailed());

        $this->subject->addResult($result);

        self::assertSame([$result], $this->subject->getFailed());
    }

    #[Framework\Attributes\Test]
    public function isSuccessfulReturnsTrueIfNoFailedCrawlingResultsAreGiven(): void
    {
        $uri = new Psr7\Uri('https://www.example.com');

        self::assertTrue($this->subject->isSuccessful());

        $this->subject->addResult(
            Src\Result\CrawlingResult::createSuccessful($uri),
        );

        self::assertTrue($this->subject->isSuccessful());

        $this->subject->addResult(
            Src\Result\CrawlingResult::createFailed($uri),
        );

        self::assertFalse($this->subject->isSuccessful());
    }

    #[Framework\Attributes\Test]
    public function wasCancelledReturnsTrueIfCacheWarmupWasCancelled(): void
    {
        self::assertFalse($this->subject->wasCancelled());

        $this->subject->setCancelled(true);

        self::assertTrue($this->subject->wasCancelled());
    }
}
