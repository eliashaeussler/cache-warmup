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
use EliasHaeussler\CacheWarmup\Tests;
use PHPUnit\Framework;

/**
 * ExceptionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\Exception::class)]
final class ExceptionTest extends Framework\TestCase
{
    use Tests\MappingErrorTrait;

    private Tests\Fixtures\Classes\DummyException $subject;

    public function setUp(): void
    {
        $this->subject = new Tests\Fixtures\Classes\DummyException();
    }

    #[Framework\Attributes\Test]
    public function formatMappingErrorReturnsFormattedErrors(): void
    {
        $error = $this->buildMappingError(['foo' => null, 'baz' => false]);

        $actual = $this->subject->formatMappingError($error);

        self::assertCount(2, $actual);
        self::assertStringContainsString('foo: Value null is not a valid string.', $actual[0]);
        self::assertMatchesRegularExpression('/Unexpected key(\(s\))? `baz`/', $actual[1]);
    }

    #[Framework\Attributes\Test]
    public function formatMappingErrorReturnsFormattedErrorsWithNodePathMapping(): void
    {
        $error = $this->buildMappingError(['foo' => null, 'baz' => false]);

        $actual = $this->subject->formatMappingError($error, ['foo' => 'baz']);

        self::assertCount(2, $actual);
        self::assertStringContainsString('baz: Value null is not a valid string.', $actual[0]);
        self::assertMatchesRegularExpression('/Unexpected key(\(s\))? `baz`/', $actual[1]);
    }
}
