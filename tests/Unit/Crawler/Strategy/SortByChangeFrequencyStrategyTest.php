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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler\Strategy;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Sitemap;
use PHPUnit\Framework;

/**
 * SortByChangeFrequencyStrategyTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SortByChangeFrequencyStrategyTest extends Framework\TestCase
{
    private Crawler\Strategy\SortByChangeFrequencyStrategy $subject;

    protected function setUp(): void
    {
        $this->subject = new Crawler\Strategy\SortByChangeFrequencyStrategy();
    }

    #[Framework\Attributes\Test]
    public function prepareUrlsSortsGivenUrlsByPriority(): void
    {
        $url1 = new Sitemap\Url('https://www.example.org/foo', changeFrequency: Sitemap\ChangeFrequency::Never);
        $url2 = new Sitemap\Url('https://www.example.org/', changeFrequency: Sitemap\ChangeFrequency::Daily);
        $url3 = new Sitemap\Url('https://www.example.org/baz', changeFrequency: Sitemap\ChangeFrequency::Always);

        self::assertSame([$url3, $url2, $url1], $this->subject->prepareUrls([$url1, $url2, $url3]));
    }
}