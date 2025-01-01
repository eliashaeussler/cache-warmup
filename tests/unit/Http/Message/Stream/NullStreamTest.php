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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message\Stream;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * NullStreamTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Stream\NullStream::class)]
final class NullStreamTest extends Framework\TestCase
{
    private Src\Http\Message\Stream\NullStream $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Http\Message\Stream\NullStream();
    }

    #[Framework\Attributes\Test]
    public function toStringReturnsEmptyString(): void
    {
        self::assertSame('', (string) $this->subject);
    }

    #[Framework\Attributes\Test]
    public function detachReturnsNull(): void
    {
        self::assertNull($this->subject->detach());
    }

    #[Framework\Attributes\Test]
    public function getSizeReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getSize());
    }

    #[Framework\Attributes\Test]
    public function eofReturnsTrue(): void
    {
        self::assertTrue($this->subject->eof());
    }

    #[Framework\Attributes\Test]
    public function isSeekableReturnsTrue(): void
    {
        self::assertTrue($this->subject->isSeekable());
    }

    #[Framework\Attributes\Test]
    public function isWritableReturnsTrue(): void
    {
        self::assertTrue($this->subject->isWritable());
    }

    #[Framework\Attributes\Test]
    public function writeReturnsStringLength(): void
    {
        self::assertSame(3, $this->subject->write('foo'));
    }

    #[Framework\Attributes\Test]
    public function isReadableReturnsTrue(): void
    {
        self::assertTrue($this->subject->isReadable());
    }

    #[Framework\Attributes\Test]
    public function readReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->read(128));
    }

    #[Framework\Attributes\Test]
    public function getContentsReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getContents());
    }

    #[Framework\Attributes\Test]
    public function getMetadataReturnsNullIfKeyIsGiven(): void
    {
        self::assertNull($this->subject->getMetadata('foo'));
    }

    #[Framework\Attributes\Test]
    public function getMetadataReturnsEmptyArrayIfNoKeyIsGiven(): void
    {
        self::assertSame([], $this->subject->getMetadata());
    }
}
