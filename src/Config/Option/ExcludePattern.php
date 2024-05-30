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

namespace EliasHaeussler\CacheWarmup\Config\Option;

use EliasHaeussler\CacheWarmup\Exception;
use Stringable;

use function fnmatch;
use function preg_match;
use function str_ends_with;
use function str_starts_with;

/**
 * ExcludePattern.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ExcludePattern
{
    /**
     * @var callable(string): bool
     */
    private $matchFunction;

    /**
     * @pure
     *
     * @param callable(string): bool $matchFunction
     */
    private function __construct(callable $matchFunction)
    {
        $this->matchFunction = $matchFunction;
    }

    /**
     * @pure
     *
     * @throws Exception\RegularExpressionIsInvalid
     */
    public static function create(string $pattern): self
    {
        if (self::isRegularExpression($pattern)) {
            return self::createFromRegularExpression($pattern);
        }

        return self::createFromPattern($pattern);
    }

    /**
     * @pure
     */
    public static function createFromPattern(string $pattern): self
    {
        return new self(
            static fn (string $url) => fnmatch($pattern, $url),
        );
    }

    /**
     * @pure
     *
     * @throws Exception\RegularExpressionIsInvalid
     */
    public static function createFromRegularExpression(string $regex): self
    {
        if (!self::isRegularExpression($regex)) {
            throw new Exception\RegularExpressionIsInvalid($regex);
        }

        /* @phpstan-ignore possiblyImpure.functionCall */
        if (false === @preg_match($regex, '')) {
            throw new Exception\RegularExpressionIsInvalid($regex);
        }

        return new self(
            static fn (string $url) => 1 === preg_match($regex, $url),
        );
    }

    public function matches(string|Stringable $url): bool
    {
        return ($this->matchFunction)((string) $url);
    }

    /**
     * @pure
     */
    private static function isRegularExpression(string $pattern): bool
    {
        return str_starts_with($pattern, '#') && str_ends_with($pattern, '#');
    }
}
