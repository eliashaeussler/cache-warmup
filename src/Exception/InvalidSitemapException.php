<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Exception;

use EliasHaeussler\CacheWarmup\Sitemap;

use function get_debug_type;

/**
 * InvalidSitemapException.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class InvalidSitemapException extends Exception
{
    public static function forInvalidType(mixed $sitemap): self
    {
        return new self(
            sprintf('Sitemaps must be of type string or %s, %s given.', Sitemap::class, get_debug_type($sitemap)),
            1604055096
        );
    }

    public static function forEmptyUrl(): self
    {
        return new self('Sitemap URL must not be empty.', 1604055264);
    }

    public static function forInvalidUrl(): self
    {
        return new self('Sitemap must be a valid URL.', 1604055334);
    }
}
