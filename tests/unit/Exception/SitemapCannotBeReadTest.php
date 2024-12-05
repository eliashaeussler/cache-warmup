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

namespace EliasHaeussler\CacheWarmup\Tests\Exception;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * SitemapCannotBeReadTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\SitemapCannotBeRead::class)]
final class SitemapCannotBeReadTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenSitemap(): void
    {
        $sitemap = Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml');

        $actual = new Src\Exception\SitemapCannotBeRead($sitemap);

        self::assertSame(1733161743, $actual->getCode());
        self::assertSame(
            'The sitemap "https://www.example.com/sitemap.xml" contains invalid XML and cannot be parsed.',
            $actual->getMessage(),
        );
    }
}
