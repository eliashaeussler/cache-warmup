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

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

use function implode;

/**
 * InvalidConfigFileExceptionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidConfigFileException::class)]
final class InvalidConfigFileExceptionTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function createReturnsExceptionForGivenConfigFileAndError(): void
    {
        $error = $this->buildMappingError();

        $expected = implode(PHP_EOL, [
            'The config file "foo" is invalid:',
            '  * foo: Value null is not a valid string.',
        ]);

        $actual = Src\Exception\InvalidConfigFileException::create('foo', $error);

        self::assertSame($expected, $actual->getMessage());
        self::assertSame(1708631576, $actual->getCode());
    }

    private function buildMappingError(): Valinor\Mapper\MappingError
    {
        try {
            (new Valinor\MapperBuilder())
                ->mapper()
                ->map('array{foo: string}', ['foo' => null]);
        } catch (Valinor\Mapper\MappingError $error) {
            return $error;
        }

        self::fail('Unable to build mapping error.');
    }
}
