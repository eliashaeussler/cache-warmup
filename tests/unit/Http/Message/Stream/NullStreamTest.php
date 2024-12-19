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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message\Stream;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

use function array_values;
use function fclose;
use function feof;
use function fflush;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function is_resource;

/**
 * NullStreamTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Stream\NullStream::class)]
final class NullStreamTest extends Framework\TestCase
{
    /**
     * @var resource
     */
    private $subject;

    public function setUp(): void
    {
        Src\Http\Message\Stream\NullStream::register();

        $resource = fopen('null:///', 'w+');

        if (!is_resource($resource)) {
            self::fail('Cannot create resource.');
        }

        $this->subject = $resource;
    }

    #[Framework\Attributes\Test]
    public function streamEofReturnsTrue(): void
    {
        self::assertTrue(feof($this->subject));
    }

    #[Framework\Attributes\Test]
    public function streamFlushReturnsTrue(): void
    {
        self::assertTrue(fflush($this->subject));
    }

    #[Framework\Attributes\Test]
    public function streamOpenOpensStream(): void
    {
        $actual = fopen('null:///foo', 'r');

        self::assertIsResource($actual);

        fclose($actual);
    }

    #[Framework\Attributes\Test]
    public function streamReadReturnsEmptyString(): void
    {
        self::assertSame('', fread($this->subject, 124));
    }

    #[Framework\Attributes\Test]
    public function streamSeekReturnsZero(): void
    {
        self::assertSame(0, fseek($this->subject, 0));
    }

    #[Framework\Attributes\Test]
    public function streamStatReturnsArrayWithEmptyInformation(): void
    {
        $blockSize = PHP_OS_FAMILY === 'Windows' ? -1 : 0;
        $statistics = [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => 0,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => $blockSize,
            'blocks' => $blockSize,
        ];

        $expected = [
            ...array_values($statistics),
            ...$statistics,
        ];

        self::assertSame($expected, fstat($this->subject));
    }

    #[Framework\Attributes\Test]
    public function streamTellReturnsZero(): void
    {
        self::assertSame(0, ftell($this->subject));
    }

    #[Framework\Attributes\Test]
    public function streamWriteReturnsZeroOnEmptyData(): void
    {
        self::assertSame(0, fwrite($this->subject, ''));
    }

    #[Framework\Attributes\Test]
    public function streamWriteReturnsNonZeroOnNonEmptyData(): void
    {
        self::assertSame(3, fwrite($this->subject, 'foo'));
    }

    protected function tearDown(): void
    {
        fclose($this->subject);

        Src\Http\Message\Stream\NullStream::unregister();
    }
}
