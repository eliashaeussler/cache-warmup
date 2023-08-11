<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Log;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;
use Psr\Log;

/**
 * LogLevelTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\LogLevel::class)]
final class LogLevelTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function satisfiesReturnsTrueIfLogLevelSatisfiesGivenLogLevel(): void
    {
        $subject = Src\Log\LogLevel::Warning;

        self::assertTrue($subject->satisfies(Src\Log\LogLevel::Error));
        self::assertFalse($subject->satisfies(Src\Log\LogLevel::Notice));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('fromNameReturnsLogLevelFromGivenNameDataProvider')]
    public function fromNameReturnsLogLevelFromGivenName(string $level, Src\Log\LogLevel $expected): void
    {
        self::assertSame($expected, Src\Log\LogLevel::fromName($level));
    }

    #[Framework\Attributes\Test]
    public function fromNameThrowsExceptionOnUnsupportedLogLevel(): void
    {
        $this->expectExceptionObject(Src\Exception\UnsupportedLogLevelException::create('foo'));

        Src\Log\LogLevel::fromName('foo');
    }

    /**
     * @phpstan-param Log\LogLevel::* $level
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('fromPsrLogLevelReturnsLogLevelFromGivenPsrLogLevelDataProvider')]
    public function fromPsrLogLevelReturnsLogLevelFromGivenPsrLogLevel(string $level, Src\Log\LogLevel $expected): void
    {
        self::assertSame($expected, Src\Log\LogLevel::fromPsrLogLevel($level));
    }

    /**
     * @return Generator<string, array{string, Src\Log\LogLevel}>
     */
    public static function fromNameReturnsLogLevelFromGivenNameDataProvider(): Generator
    {
        yield 'emergency' => ['emergency', Src\Log\LogLevel::Emergency];
        yield 'alert' => ['alert', Src\Log\LogLevel::Alert];
        yield 'critical' => ['critical', Src\Log\LogLevel::Critical];
        yield 'error' => ['error', Src\Log\LogLevel::Error];
        yield 'warning' => ['warning', Src\Log\LogLevel::Warning];
        yield 'notice' => ['notice', Src\Log\LogLevel::Notice];
        yield 'info' => ['info', Src\Log\LogLevel::Info];
        yield 'debug' => ['debug', Src\Log\LogLevel::Debug];
    }

    /**
     * @return Generator<string, array{Log\LogLevel::*, Src\Log\LogLevel}>
     */
    public static function fromPsrLogLevelReturnsLogLevelFromGivenPsrLogLevelDataProvider(): Generator
    {
        yield 'emergency' => [Log\LogLevel::EMERGENCY, Src\Log\LogLevel::Emergency];
        yield 'alert' => [Log\LogLevel::ALERT, Src\Log\LogLevel::Alert];
        yield 'critical' => [Log\LogLevel::CRITICAL, Src\Log\LogLevel::Critical];
        yield 'error' => [Log\LogLevel::ERROR, Src\Log\LogLevel::Error];
        yield 'warning' => [Log\LogLevel::WARNING, Src\Log\LogLevel::Warning];
        yield 'notice' => [Log\LogLevel::NOTICE, Src\Log\LogLevel::Notice];
        yield 'info' => [Log\LogLevel::INFO, Src\Log\LogLevel::Info];
        yield 'debug' => [Log\LogLevel::DEBUG, Src\Log\LogLevel::Debug];
    }
}
