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

namespace EliasHaeussler\CacheWarmup\Xml\Node;

use function str_replace;

/**
 * SitemapNode.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
enum SitemapNode: string
{
    case ChangeFrequency = 'changefreq';
    case LastModificationDate = 'lastmod';
    case Location = 'loc';
    case Priority = 'priority';

    public function asPath(SitemapNodePath $base): string
    {
        return $base->value.'/'.$this->value;
    }

    public static function tryFromPath(string $nodePath, SitemapNodePath $base): ?self
    {
        $node = str_replace($base->value.'/', '', $nodePath);

        return self::tryFrom($node);
    }
}
