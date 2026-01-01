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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Component;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * OptionsParserTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Component\OptionsParser::class)]
final class OptionsParserTest extends Framework\TestCase
{
    private Src\Config\Component\OptionsParser $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Config\Component\OptionsParser();
    }

    #[Framework\Attributes\Test]
    public function parseReturnsEmptyArrayOnNull(): void
    {
        self::assertSame([], $this->subject->parse(null));
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnMalformedJson(): void
    {
        $this->expectException(Src\Exception\OptionsAreMalformed::class);

        $this->subject->parse('');
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionIfJsonEncodedOptionsAreInvalid(): void
    {
        $this->expectException(Src\Exception\OptionsAreMalformed::class);

        $this->subject->parse('"foo"');
    }

    #[Framework\Attributes\Test]
    public function parseThrowsExceptionOnNonAssociativeArray(): void
    {
        $this->expectException(Src\Exception\OptionsAreInvalid::class);

        $this->subject->parse(['foo']);
    }

    #[Framework\Attributes\Test]
    public function parseCanUseJsonEncodedString(): void
    {
        $options = '{"foo":"baz"}';
        $expected = ['foo' => 'baz'];

        self::assertSame($expected, $this->subject->parse($options));
    }

    #[Framework\Attributes\Test]
    public function parseReturnsParsedOptions(): void
    {
        $options = ['foo' => 'baz'];
        $expected = ['foo' => 'baz'];

        self::assertSame($expected, $this->subject->parse($options));
    }
}
