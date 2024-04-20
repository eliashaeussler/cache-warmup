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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler\Strategy;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use PHPUnit\Framework;

/**
 * SortingStrategyTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\Strategy\SortingStrategy::class)]
final class SortingStrategyTest extends Framework\TestCase
{
    private Tests\Fixtures\Classes\DummySortingStrategy $subject;

    protected function setUp(): void
    {
        $this->subject = new Tests\Fixtures\Classes\DummySortingStrategy();
    }

    #[Framework\Attributes\Test]
    public function prepareUrlsSortsUrlsBySortingImplementation(): void
    {
        $urls = [
            new Src\Sitemap\Url('https://www.example.com/'),
            new Src\Sitemap\Url('https://www.example.com/foo'),
            new Src\Sitemap\Url('https://www.example.com/baz'),
        ];

        $expected = [
            new Src\Sitemap\Url('https://www.example.com/'),
            new Src\Sitemap\Url('https://www.example.com/baz'),
            new Src\Sitemap\Url('https://www.example.com/foo'),
        ];

        self::assertEquals($expected, $this->subject->prepareUrls($urls));
    }
}
