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
use PHPUnit\Framework;

use function implode;

/**
 * EnvironmentVariablesAreInvalidTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\EnvironmentVariablesAreInvalid::class)]
final class EnvironmentVariablesAreInvalidTest extends Framework\TestCase
{
    use Tests\MappingErrorTrait;

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForGivenErrors(): void
    {
        $error = $this->buildMappingError();
        $nameMapping = [
            'foo' => 'CACHE_WARMUP_FOO',
        ];

        $expected = implode(PHP_EOL, [
            'Some environment variables are invalid:',
            '  * CACHE_WARMUP_FOO: Value null is not a valid string.',
        ]);

        $actual = new Src\Exception\EnvironmentVariablesAreInvalid($error, $nameMapping);

        self::assertSame($expected, $actual->getMessage());
        self::assertSame(1708635629, $actual->getCode());
    }
}
