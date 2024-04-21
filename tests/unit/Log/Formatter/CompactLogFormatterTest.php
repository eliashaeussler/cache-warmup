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

namespace EliasHaeussler\CacheWarmup\Tests\Log\Formatter;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;
use Psr\Log;
use stdClass;

/**
 * CompactLogFormatterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Formatter\CompactLogFormatter::class)]
final class CompactLogFormatterTest extends Framework\TestCase
{
    private Src\Log\Formatter\CompactLogFormatter $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Log\Formatter\CompactLogFormatter();
    }

    #[Framework\Attributes\Test]
    public function formatReplacesPlaceholdersInMessage(): void
    {
        $context = [
            'foo' => 'baz',
        ];

        self::assertStringEndsWith(
            'ERROR: oops, a baz occurred. []',
            $this->subject->format(Log\LogLevel::ERROR, 'oops, a {foo} occurred.', $context),
        );
    }

    #[Framework\Attributes\Test]
    public function formatPrintsContextWithNonReplacedPlaceholders(): void
    {
        $context = [
            'foo' => 'baz',
            'baz' => 'foo',
        ];

        self::assertStringEndsWith(
            'ERROR: oops, a baz occurred. {"baz":"foo"}',
            $this->subject->format(Log\LogLevel::ERROR, 'oops, a {foo} occurred.', $context),
        );
    }

    #[Framework\Attributes\Test]
    public function formatSkipsNonStringablePlaceholderValues(): void
    {
        $context = [
            'foo' => new stdClass(),
        ];

        self::assertStringEndsWith(
            'ERROR: oops, a {foo} occurred. {"foo":"stdClass"}',
            $this->subject->format(Log\LogLevel::ERROR, 'oops, a {foo} occurred.', $context),
        );
    }

    #[Framework\Attributes\Test]
    public function formatSkipsPlaceholdersWithoutValueInContext(): void
    {
        self::assertStringEndsWith(
            'ERROR: oops, a {foo} occurred. []',
            $this->subject->format(Log\LogLevel::ERROR, 'oops, a {foo} occurred.'),
        );
    }
}
