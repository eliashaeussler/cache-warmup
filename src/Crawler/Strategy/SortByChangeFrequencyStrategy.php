<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Crawler\Strategy;

use EliasHaeussler\CacheWarmup\Sitemap;

/**
 * SortByChangeFrequencyStrategy.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SortByChangeFrequencyStrategy extends SortingStrategy
{
    public static function getName(): string
    {
        return 'sort-by-changefreq';
    }

    protected function sortUrls(Sitemap\Url $a, Sitemap\Url $b): int
    {
        return $this->mapChangeFrequency($a->getChangeFrequency())
            <=> $this->mapChangeFrequency($b->getChangeFrequency());
    }

    private function mapChangeFrequency(?Sitemap\ChangeFrequency $changeFrequency): int
    {
        return match ($changeFrequency) {
            Sitemap\ChangeFrequency::Always => 0,
            Sitemap\ChangeFrequency::Hourly => 10,
            Sitemap\ChangeFrequency::Daily => 20,
            Sitemap\ChangeFrequency::Weekly => 30,
            Sitemap\ChangeFrequency::Monthly => 40,
            Sitemap\ChangeFrequency::Yearly => 50,
            default => 100,
        };
    }
}
