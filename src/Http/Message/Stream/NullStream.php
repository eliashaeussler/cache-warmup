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

namespace EliasHaeussler\CacheWarmup\Http\Message\Stream;

use function in_array;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * NullStream.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class NullStream
{
    private const PROTOCOL = 'null';

    /**
     * @var resource|null
     */
    public $context;

    public static function register(): void
    {
        if (!self::isRegistered()) {
            stream_wrapper_register(self::PROTOCOL, self::class);
        }
    }

    public static function unregister(): void
    {
        if (self::isRegistered()) {
            stream_wrapper_unregister(self::PROTOCOL);
        }
    }

    private static function isRegistered(): bool
    {
        return in_array(self::PROTOCOL, stream_get_wrappers(), true);
    }

    public function stream_close(): void {}

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_flush(): bool
    {
        return true;
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        return true;
    }

    public function stream_read(int $count): string
    {
        return '';
    }

    public function stream_seek(int $count, int $whence = SEEK_SET): bool
    {
        return true;
    }

    /**
     * @return array{}
     */
    public function stream_stat(): array
    {
        return [];
    }

    public function stream_tell(): int
    {
        return 0;
    }

    public function stream_write(string $data): int
    {
        // 1 is enough for curl handler to not fail during writing
        return '' === $data ? 0 : 1;
    }
}
