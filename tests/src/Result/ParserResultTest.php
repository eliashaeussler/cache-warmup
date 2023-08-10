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

namespace EliasHaeussler\CacheWarmup\Tests\Result;

use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * ParserResultTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Result\ParserResult::class)]
final class ParserResultTest extends Framework\TestCase
{
    private Src\Result\ParserResult $subject;

    protected function setUp(): void
    {
        $sitemaps = [
            new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/')),
            new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/')),
        ];
        $urls = [
            new Src\Sitemap\Url('https://www.example.com/'),
            new Src\Sitemap\Url('https://www.example.org/'),
        ];

        $this->subject = new Src\Result\ParserResult($sitemaps, $urls);
    }

    #[Framework\Attributes\Test]
    public function getSitemapsReturnsSitemaps(): void
    {
        self::assertEquals(
            [
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/')),
                new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/')),
            ],
            $this->subject->getSitemaps(),
        );
    }

    #[Framework\Attributes\Test]
    public function getUrlsReturnsUrls(): void
    {
        self::assertEquals(
            [
                new Src\Sitemap\Url('https://www.example.com/'),
                new Src\Sitemap\Url('https://www.example.org/'),
            ],
            $this->subject->getUrls(),
        );
    }
}
