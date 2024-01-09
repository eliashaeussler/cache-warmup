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

namespace EliasHaeussler\CacheWarmup\Tests\Sitemap;

use DateTimeImmutable;
use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * SitemapTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Sitemap\Sitemap::class)]
final class SitemapTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenUriIsEmpty(): void
    {
        $this->expectException(Src\Exception\InvalidUrlException::class);
        $this->expectExceptionCode(1604055264);
        $this->expectExceptionMessage('The given URL must not be empty.');

        new Src\Sitemap\Sitemap(new Psr7\Uri(''));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenUriIsNotValid(): void
    {
        $this->expectException(Src\Exception\InvalidUrlException::class);
        $this->expectExceptionCode(1604055334);
        $this->expectExceptionMessage('The given URL "foo" is not valid.');

        new Src\Sitemap\Sitemap(new Psr7\Uri('foo'));
    }

    #[Framework\Attributes\Test]
    public function constructorAssignsUriCorrectly(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $subject = new Src\Sitemap\Sitemap($uri);

        self::assertSame($uri, $subject->getUri());
    }

    #[Framework\Attributes\Test]
    public function constructorAssignsLastModificationDateCorrectly(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $lastModificationDate = (new DateTimeImmutable())->modify('- 1 day');
        $subject = new Src\Sitemap\Sitemap($uri, $lastModificationDate);

        self::assertSame($lastModificationDate, $subject->getLastModificationDate());
    }

    #[Framework\Attributes\Test]
    public function constructorAssignsOriginCorrectly(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://baz.foo'));
        $subject = new Src\Sitemap\Sitemap($uri, origin: $origin);

        self::assertSame($origin, $subject->getOrigin());
    }

    #[Framework\Attributes\Test]
    public function getRootOriginReturnsRootOrigin(): void
    {
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://baz.foo'));
        $origin2 = new Src\Sitemap\Sitemap(new Psr7\Uri('https://baz.foo'), origin: $origin);
        $origin3 = new Src\Sitemap\Sitemap(new Psr7\Uri('https://baz.foo'), origin: $origin2);
        $subject = new Src\Sitemap\Sitemap(new Psr7\Uri('https://foo.baz'), origin: $origin3);

        self::assertSame($origin, $subject->getRootOrigin());
    }

    #[Framework\Attributes\Test]
    public function setOriginAssignsOriginCorrectly(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://baz.foo'));
        $subject = new Src\Sitemap\Sitemap($uri);

        self::assertNull($subject->getOrigin());

        $subject->setOrigin($origin);

        self::assertSame($origin, $subject->getOrigin());
    }

    #[Framework\Attributes\Test]
    public function stringRepresentationReturnsUri(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $subject = new Src\Sitemap\Sitemap($uri);

        self::assertSame((string) $uri, (string) $subject);
    }
}
