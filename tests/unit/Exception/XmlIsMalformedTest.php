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
use PHPUnit\Framework;

/**
 * XmlIsMalformedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\XmlIsMalformed::class)]
final class XmlIsMalformedTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructoCreatesExceptionForGivenInputString(): void
    {
        $actual = new Src\Exception\XmlIsMalformed('foo', 'Invalid XML.');

        self::assertSame(1670962571, $actual->getCode());
        self::assertSame('The string "foo" does not contain valid XML: Invalid XML.', $actual->getMessage());
    }
}
