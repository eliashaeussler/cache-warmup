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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Option;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;

/**
 * ExcludePatternTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Option\ExcludePattern::class)]
final class ExcludePatternTest extends Framework\TestCase
{
    private Src\Config\Option\ExcludePattern $subject;

    public function setUp(): void
    {
        $this->subject = Src\Config\Option\ExcludePattern::create('*foo*');
    }

    #[Framework\Attributes\Test]
    public function createFromPatternReturnsObjectWithMatchFunctionForGivenPattern(): void
    {
        $actual = Src\Config\Option\ExcludePattern::createFromPattern('*foo*');

        self::assertTrue($actual->matches('https://www.example.com/foo'));
    }

    #[Framework\Attributes\Test]
    public function createFromRegularExpressionReturnsObjectWithMatchFunctionForGivenRegularExpression(): void
    {
        $actual = Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#');

        self::assertTrue($actual->matches('https://www.example.com/foo'));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('createFromRegularExpressionThrowsExceptionOnInvalidRegularExpressionDataProvider')]
    public function createFromRegularExpressionThrowsExceptionOnInvalidRegularExpression(string $regex): void
    {
        $this->expectExceptionObject(new Src\Exception\RegularExpressionIsInvalid($regex));

        Src\Config\Option\ExcludePattern::createFromRegularExpression($regex);
    }

    #[Framework\Attributes\Test]
    public function matchesReturnsTrueIfGivenUrlMatchesConfiguredPattern(): void
    {
        self::assertTrue($this->subject->matches('foo'));
        self::assertFalse($this->subject->matches('baz'));
    }

    #[Framework\Attributes\Test]
    public function matchesSupportsStringableObjects(): void
    {
        $url = new Src\Sitemap\Url('https://www.example.com/foo');

        self::assertTrue($this->subject->matches($url));
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function createFromRegularExpressionThrowsExceptionOnInvalidRegularExpressionDataProvider(): Generator
    {
        yield 'unsupported delimiter' => ['foo'];
        yield 'invalid expression' => ['#(foo#'];
    }
}
