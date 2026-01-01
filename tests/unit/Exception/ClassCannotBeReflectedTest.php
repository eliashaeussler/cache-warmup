<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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
use Exception;
use PHPUnit\Framework;
use stdClass;

/**
 * ClassCannotBeReflectedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\ClassCannotBeReflected::class)]
final class ClassCannotBeReflectedTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenClassName(): void
    {
        $previous = new Exception();

        $actual = new Src\Exception\ClassCannotBeReflected(stdClass::class, $previous);

        self::assertSame('There was an error when trying to reflect class "stdClass".', $actual->getMessage());
        self::assertSame(1740467760, $actual->getCode());
        self::assertSame($previous, $actual->getPrevious());
    }
}
