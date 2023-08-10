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

use DateTime;
use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Sitemap;
use PHPUnit\Framework;

/**
 * SortByLastModificationDateStrategyTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Crawler\Strategy\SortByLastModificationDateStrategy::class)]
final class SortByLastModificationDateStrategyTest extends Framework\TestCase
{
    private Crawler\Strategy\SortByLastModificationDateStrategy $subject;

    protected function setUp(): void
    {
        $this->subject = new Crawler\Strategy\SortByLastModificationDateStrategy();
    }

    #[Framework\Attributes\Test]
    public function prepareUrlsSortsGivenUrlsByPriority(): void
    {
        $url1 = new Sitemap\Url('https://www.example.org/foo');
        $url2 = new Sitemap\Url('https://www.example.org/', lastModificationDate: new DateTime('last year'));
        $url3 = new Sitemap\Url('https://www.example.org/baz', lastModificationDate: new DateTime('last month'));

        self::assertSame([$url3, $url2, $url1], $this->subject->prepareUrls([$url1, $url2, $url3]));
    }
}
