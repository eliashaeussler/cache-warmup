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
 * InvalidUrlExceptionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidUrlException::class)]
final class InvalidUrlExceptionTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function createReturnsExceptionForGivenUrl(): void
    {
        $actual = Src\Exception\InvalidUrlException::create('foo');

        self::assertSame(1604055334, $actual->getCode());
        self::assertSame('The given URL "foo" is not valid.', $actual->getMessage());
    }

    #[Framework\Attributes\Test]
    public function forEmptyUrlReturnsExceptionIfUrlIsEmpty(): void
    {
        $actual = Src\Exception\InvalidUrlException::forEmptyUrl();

        self::assertSame(1604055264, $actual->getCode());
        self::assertSame('The given URL must not be empty.', $actual->getMessage());
    }
}
