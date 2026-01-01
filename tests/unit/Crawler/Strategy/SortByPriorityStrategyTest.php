<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler\Strategy;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * SortByPriorityStrategyTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\Strategy\SortByPriorityStrategy::class)]
final class SortByPriorityStrategyTest extends Framework\TestCase
{
    private Src\Crawler\Strategy\SortByPriorityStrategy $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Crawler\Strategy\SortByPriorityStrategy();
    }

    #[Framework\Attributes\Test]
    public function prepareUrlsSortsGivenUrlsByPriority(): void
    {
        $url1 = new Src\Sitemap\Url('https://www.example.org/foo', 0.75);
        $url2 = new Src\Sitemap\Url('https://www.example.org/', 0.5);
        $url3 = new Src\Sitemap\Url('https://www.example.org/baz', 1.0);

        self::assertSame([$url3, $url1, $url2], $this->subject->prepareUrls([$url1, $url2, $url3]));
    }
}
