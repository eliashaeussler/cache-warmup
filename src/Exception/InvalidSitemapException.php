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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Exception;

use EliasHaeussler\CacheWarmup\Sitemap;
use Throwable;

use function get_debug_type;
use function sprintf;

/**
 * InvalidSitemapException.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class InvalidSitemapException extends Exception
{
    public static function create(Sitemap\Sitemap $sitemap, Throwable $previous = null): self
    {
        return new self(
            sprintf('The sitemap "%s" is invalid and cannot be parsed.', $sitemap->getUri()),
            1660668799,
            $previous
        );
    }

    public static function forInvalidType(mixed $sitemap): self
    {
        return new self(
            sprintf('Sitemaps must be of type string or %s, %s given.', Sitemap\Sitemap::class, get_debug_type($sitemap)),
            1604055096
        );
    }
}
