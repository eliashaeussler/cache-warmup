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

/**
 * ArrayHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\ArrayHelper::class)]
final class ArrayHelperTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function getValueByPathReturnsValueAtGivenPath(): void
    {
        $subject = [
            'foo' => [
                'bar' => 'hello world!',
            ],
        ];

        self::assertSame('hello world!', Src\Helper\ArrayHelper::getValueByPath($subject, 'foo/bar'));
        self::assertSame(['bar' => 'hello world!'], Src\Helper\ArrayHelper::getValueByPath($subject, 'foo'));
        self::assertNull(Src\Helper\ArrayHelper::getValueByPath($subject, 'bar'));
    }

    #[Framework\Attributes\Test]
    public function setValueByPathSetsValueAtGivenPath(): void
    {
        $subject = [
            'foo' => [
                'bar' => 'hello world!',
            ],
        ];

        Src\Helper\ArrayHelper::setValueByPath($subject, 'foo/bar', 'bye!');

        self::assertSame('bye!', Src\Helper\ArrayHelper::getValueByPath($subject, 'foo/bar'));

        Src\Helper\ArrayHelper::setValueByPath($subject, 'bar', 'hello world!');

        self::assertSame('hello world!', Src\Helper\ArrayHelper::getValueByPath($subject, 'bar'));
        self::assertNull(Src\Helper\ArrayHelper::getValueByPath($subject, 'foobar'));
        self::assertSame(
            [
                'foo' => [
                    'bar' => 'bye!',
                ],
                'bar' => 'hello world!',
            ],
            $subject,
        );
    }

    #[Framework\Attributes\Test]
    public function mergeRecursiveMergesGivenArrayIntoOriginalArray(): void
    {
        $subject = [
            'foo' => [
                'baz',
                'bar' => [
                    'hello world',
                ],
            ],
        ];

        $other = [
            'baz' => 'hello world',
            'foo' => [
                'hello world',
                'bar' => [
                    'foo',
                ],
            ],
        ];

        $expected = [
            'foo' => [
                'baz',
                'bar' => [
                    'hello world',
                    'foo',
                ],
                'hello world',
            ],
            'baz' => 'hello world',
        ];

        Src\Helper\ArrayHelper::mergeRecursive($subject, $other);

        self::assertSame($expected, $subject);
    }
}
