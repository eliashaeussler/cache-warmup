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

namespace EliasHaeussler\CacheWarmup\Helper;

use function is_array;

/**
 * ArrayHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ArrayHelper
{
    /**
     * @param iterable<string, mixed> $subject
     * @param non-empty-string        $delimiter
     */
    public static function getValueByPath(iterable $subject, string $path, string $delimiter = '/'): mixed
    {
        $pathSegments = array_filter(explode($delimiter, $path));
        $reference = &$subject;

        foreach ($pathSegments as $pathSegment) {
            if (!self::pathSegmentExists($reference, $pathSegment)) {
                return null;
            }

            $reference = &$reference[$pathSegment];
        }

        return $reference;
    }

    /**
     * @param iterable<string, mixed> $subject
     * @param non-empty-string        $delimiter
     */
    public static function setValueByPath(iterable &$subject, string $path, mixed $value, string $delimiter = '/'): void
    {
        $pathSegments = array_filter(explode($delimiter, $path));
        $reference = &$subject;

        foreach ($pathSegments as $pathSegment) {
            if (!self::pathSegmentExists($reference, $pathSegment)) {
                $reference[$pathSegment] = [];
            }

            $reference = &$reference[$pathSegment];
        }

        $reference = $value;
    }

    private static function pathSegmentExists(mixed $subject, string $pathSegment): bool
    {
        return is_array($subject) && array_key_exists($pathSegment, $subject);
    }
}
