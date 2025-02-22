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

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

use function implode;

/**
 * SitemapCannotBeParsedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\SitemapCannotBeParsed::class)]
final class SitemapCannotBeParsedTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForGivenSitemap(): void
    {
        $sitemap = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com'));
        $actual = new Src\Exception\SitemapCannotBeParsed($sitemap);

        self::assertSame(1660668799, $actual->getCode());
        self::assertSame(
            'The sitemap "https://www.example.com" is invalid and cannot be parsed.',
            $actual->getMessage(),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorCreatesExceptionForGivenSitemapAndMappingError(): void
    {
        $sitemap = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com'));
        $error = $this->createMappingError();
        $actual = new Src\Exception\SitemapCannotBeParsed($sitemap, $error);

        self::assertSame(1660668799, $actual->getCode());
        self::assertSame(
            implode(PHP_EOL, [
                'The sitemap "https://www.example.com" is invalid and cannot be parsed due to the following errors:',
                '  * Cannot be empty and must be filled with a value matching type `string`.',
            ]),
            $actual->getMessage(),
        );
    }

    private function createMappingError(): Valinor\Mapper\MappingError
    {
        try {
            (new Valinor\MapperBuilder())
                ->mapper()
                ->map('array{foo: string}', [])
            ;
        } catch (Valinor\Mapper\MappingError $error) {
            return $error;
        }

        self::fail('Expected error was not thrown.');
    }
}
