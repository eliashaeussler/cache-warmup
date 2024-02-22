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

namespace EliasHaeussler\CacheWarmup\Helper;

use function array_filter;
use function array_key_exists;
use function explode;
use function is_array;
use function is_int;

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

    /**
     * @param array<array-key, mixed> $subject
     * @param array<array-key, mixed> $other
     */
    public static function mergeRecursive(array &$subject, array $other): void
    {
        foreach ($other as $key => $value) {
            // Skip merge if key does not exist in subject
            if (!array_key_exists($key, $subject)) {
                $subject[$key] = $value;
                continue;
            }

            // Append value if key is numeric
            if (is_int($key)) {
                $subject[] = $value;
                continue;
            }

            $originalValue = &$subject[$key];

            // Overwrite value in subject
            if (!is_array($value)) {
                $originalValue = $value;
                continue;
            }

            // Merge arrays
            if (is_array($originalValue)) {
                self::mergeRecursive($originalValue, $value);
            }
        }
    }

    private static function pathSegmentExists(mixed $subject, string $pathSegment): bool
    {
        return is_array($subject) && array_key_exists($pathSegment, $subject);
    }
}
