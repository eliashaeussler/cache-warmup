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

namespace EliasHaeussler\CacheWarmup\Tests\Exception;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * OptionsAreMalformedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\OptionsAreMalformed::class)]
final class OptionsAreMalformedTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForMalformedOptions(): void
    {
        $actual = new Src\Exception\OptionsAreMalformed('foo');

        self::assertSame(1734462725, $actual->getCode());
        self::assertSame('Options "foo" are malformed and cannot be parsed.', $actual->getMessage());
    }

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForMalformedOptionsWithNonScalarSource(): void
    {
        $actual = new Src\Exception\OptionsAreMalformed($this);

        self::assertSame(1734462725, $actual->getCode());
        self::assertSame('Options are malformed and cannot be parsed.', $actual->getMessage());
    }
}
