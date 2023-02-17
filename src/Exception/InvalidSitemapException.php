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

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup\Sitemap;

use function array_map;
use function get_debug_type;
use function implode;
use function sprintf;

/**
 * InvalidSitemapException.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class InvalidSitemapException extends Exception
{
    public static function create(Sitemap\Sitemap $sitemap, Valinor\Mapper\MappingError $error = null): self
    {
        $suffix = '.';

        if (null !== $error) {
            $suffix = ' due to the following errors:'.PHP_EOL.self::formatError($error);
        }

        return new self(
            sprintf('The sitemap "%s" is invalid and cannot be parsed%s', $sitemap->getUri(), $suffix),
            1660668799,
            $error,
        );
    }

    public static function forInvalidType(mixed $sitemap): self
    {
        return new self(
            sprintf('Sitemaps must be of type string or %s, %s given.', Sitemap\Sitemap::class, get_debug_type($sitemap)),
            1604055096
        );
    }

    private static function formatError(Valinor\Mapper\MappingError $error): string
    {
        $messages = Valinor\Mapper\Tree\Message\Messages::flattenFromNode($error->node());

        return implode(
            PHP_EOL,
            array_map(
                static fn (Valinor\Mapper\Tree\Message\NodeMessage $message) => '  * '.$message->toString(),
                $messages->toArray(),
            ),
        );
    }
}
