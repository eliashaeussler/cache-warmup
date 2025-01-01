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

namespace EliasHaeussler\CacheWarmup\Tests\Formatter;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * FormatterFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Formatter\FormatterFactory::class)]
final class FormatterFactoryTest extends Framework\TestCase
{
    private Src\Formatter\FormatterFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Formatter\FormatterFactory(
            new Console\Style\SymfonyStyle(
                new Console\Input\StringInput(''),
                new Console\Output\BufferedOutput(),
            ),
        );
    }

    /**
     * @param class-string<Src\Formatter\Formatter> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getReturnsFormatterOfGivenTypeDataProvider')]
    public function getReturnsFormatterOfGivenType(string $type, string $expected): void
    {
        self::assertInstanceOf($expected, $this->subject->get($type));
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenTypeIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\FormatterIsNotSupported('foo'));

        $this->subject->get('foo');
    }

    /**
     * @return Generator<string, array{string, class-string<Src\Formatter\Formatter>}>
     */
    public static function getReturnsFormatterOfGivenTypeDataProvider(): Generator
    {
        yield 'json' => ['json', Src\Formatter\JsonFormatter::class];
        yield 'text' => ['text', Src\Formatter\TextFormatter::class];
    }
}
