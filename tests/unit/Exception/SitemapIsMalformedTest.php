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

namespace EliasHaeussler\CacheWarmup\Tests\Exception;

use EliasHaeussler\CacheWarmup as Src;
use Exception;
use LibXMLError;
use PHPUnit\Framework;

use function implode;

/**
 * SitemapIsMalformedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\SitemapIsMalformed::class)]
final class SitemapIsMalformedTest extends Framework\TestCase
{
    private Src\Sitemap\Sitemap $sitemap;

    protected function setUp(): void
    {
        $this->sitemap = Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml');
    }

    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenSitemap(): void
    {
        $actual = new Src\Exception\SitemapIsMalformed($this->sitemap);

        self::assertSame(1733161983, $actual->getCode());
        self::assertSame(
            'Sitemap "https://www.example.com/sitemap.xml" is malformed and cannot be parsed.',
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenSitemapAndErrors(): void
    {
        $error1 = new LibXMLError();
        $error1->message = 'foo';
        $error1->line = 1;

        $error2 = new LibXMLError();
        $error2->message = 'baz';
        $error2->line = 7;

        $errors = [
            $error1,
            $error2,
        ];

        $actual = new Src\Exception\SitemapIsMalformed($this->sitemap, $errors);

        self::assertSame(1733161983, $actual->getCode());
        self::assertSame(
            implode(PHP_EOL, [
                'Sitemap "https://www.example.com/sitemap.xml" is malformed and cannot be parsed:',
                '  * Line 1: foo',
                '  * Line 7: baz',
            ]),
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenSitemapAndPreviousException(): void
    {
        $previous = new Exception('foo baz');

        $actual = new Src\Exception\SitemapIsMalformed($this->sitemap, previous: $previous);

        self::assertSame(1733161983, $actual->getCode());
        self::assertSame(
            'Sitemap "https://www.example.com/sitemap.xml" is malformed and cannot be parsed:'.PHP_EOL.'  * foo baz',
            $actual->getMessage(),
        );
        self::assertSame($previous, $actual->getPrevious());
    }
}
