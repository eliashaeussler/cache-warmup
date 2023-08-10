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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Exception;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * InvalidCrawlerExceptionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidCrawlerException::class)]
final class InvalidCrawlerExceptionTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function forMissingClassReturnsExceptionForGivenClass(): void
    {
        $actual = Src\Exception\InvalidCrawlerException::forMissingClass('foo');

        self::assertSame(1604261816, $actual->getCode());
        self::assertSame('The specified crawler class "foo" does not exist.', $actual->getMessage());
    }

    #[Framework\Attributes\Test]
    public function forUnsupportedClassReturnsExceptionForGivenClass(): void
    {
        $actual = Src\Exception\InvalidCrawlerException::forUnsupportedClass('foo');

        self::assertSame(1604261885, $actual->getCode());
        self::assertSame('The specified crawler "foo" is not valid.', $actual->getMessage());
    }
}
