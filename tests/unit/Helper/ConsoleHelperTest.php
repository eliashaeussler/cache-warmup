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

namespace EliasHaeussler\CacheWarmup\Tests\Helper;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * ConsoleHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\ConsoleHelper::class)]
final class ConsoleHelperTest extends Framework\TestCase
{
    private Console\Formatter\OutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new Console\Formatter\OutputFormatter();
    }

    #[Framework\Attributes\Test]
    public function registerAdditionalConsoleOutputStylesRegistersAdditionalOutputStyles(): void
    {
        self::assertFalse($this->formatter->hasStyle('success'));
        self::assertFalse($this->formatter->hasStyle('failure'));
        self::assertFalse($this->formatter->hasStyle('skipped'));

        Src\Helper\ConsoleHelper::registerAdditionalConsoleOutputStyles($this->formatter);

        self::assertTrue($this->formatter->hasStyle('success'));
        self::assertTrue($this->formatter->hasStyle('failure'));
        self::assertTrue($this->formatter->hasStyle('skipped'));
    }
}
