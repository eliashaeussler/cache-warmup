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

namespace EliasHaeussler\CacheWarmup\Http\Message\Stream;

use Psr\Http\Message;

use function strlen;

/**
 * NullStream.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class NullStream implements Message\StreamInterface
{
    public function __toString(): string
    {
        return '';
    }

    public function close(): void {}

    public function detach()
    {
        return null;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void {}

    public function rewind(): void {}

    public function isWritable(): bool
    {
        return true;
    }

    public function write(string $string): int
    {
        return strlen($string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        return '';
    }

    public function getContents(): string
    {
        return '';
    }

    /**
     * @return array{}|null
     */
    public function getMetadata(?string $key = null): ?array
    {
        return null !== $key ? null : [];
    }
}
