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

namespace EliasHaeussler\CacheWarmup\Exception;

use EliasHaeussler\CacheWarmup\Sitemap;
use Throwable;

use function sprintf;

/**
 * SitemapIsMalformed.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SitemapIsMalformed extends Exception
{
    public function __construct(Sitemap\Sitemap $sitemap, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Sitemap "%s" is malformed and cannot be parsed.',
                $sitemap->isLocalFile() ? $sitemap->getLocalFilePath() : (string) $sitemap,
            ),
            1733161983,
            $previous,
        );
    }
}
