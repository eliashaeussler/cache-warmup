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

namespace EliasHaeussler\CacheWarmup\Tests\Exception;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Exception;
use PHPUnit\Framework;

/**
 * OptionsAreInvalidTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\OptionsAreInvalid::class)]
final class OptionsAreInvalidTest extends Framework\TestCase
{
    use Tests\MappingErrorTrait;

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForInvalidOptions(): void
    {
        $actual = new Src\Exception\OptionsAreInvalid();

        self::assertSame(1677424305, $actual->getCode());
        self::assertSame('Some options are invalid.', $actual->getMessage());
    }

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForInvalidOptionsAndError(): void
    {
        $error = $this->buildMappingError();

        $actual = new Src\Exception\OptionsAreInvalid($error);

        self::assertSame(
            'Some options are invalid:'.PHP_EOL.'  * foo: Value null is not a valid string.',
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForInvalidOptionsAndException(): void
    {
        $previous = new Exception('Something went wrong.');
        $actual = new Src\Exception\OptionsAreInvalid(previous: $previous);

        self::assertSame(
            'Some options are invalid:'.PHP_EOL.'Something went wrong.',
            $actual->getMessage(),
        );
        self::assertSame($previous, $actual->getPrevious());
    }
}
