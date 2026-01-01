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

namespace EliasHaeussler\CacheWarmup\Helper;

use EliasHaeussler\CacheWarmup\Exception;
use Symfony\Component\Filesystem;

use function implode;
use function preg_replace;
use function rtrim;

/**
 * FilesystemHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class FilesystemHelper
{
    public static function resolveRelativePath(string $relativePath): string
    {
        if (Filesystem\Path::isAbsolute($relativePath)) {
            return $relativePath;
        }

        return self::joinPathSegments(
            Filesystem\Path::makeAbsolute($relativePath, self::getWorkingDirectory()),
        );
    }

    public static function getWorkingDirectory(): string
    {
        $cwd = realpath((string) getcwd());

        if (false === $cwd) {
            throw new Exception\WorkingDirectoryCannotBeResolved();
        }

        return self::joinPathSegments(
            Filesystem\Path::canonicalize($cwd),
        );
    }

    public static function joinPathSegments(string ...$pathSegments): string
    {
        return rtrim(
            (string) preg_replace(
                '#[/\\\]+#',
                DIRECTORY_SEPARATOR,
                implode(DIRECTORY_SEPARATOR, $pathSegments),
            ),
            '/\\',
        );
    }
}
