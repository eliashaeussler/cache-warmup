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

namespace EliasHaeussler\CacheWarmup\Tests\Sitemap;

use DateTimeImmutable;
use EliasHaeussler\CacheWarmup as Src;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

use function dirname;
use function urlencode;

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
    public function constructorConvertsLegacyLocalFileNotation(): void
    {
        $uri = 'local://file?path='.urlencode('/foo/baz');
        $subject = new Src\Sitemap\Sitemap(new Psr7\Uri('file:///foo/baz'));

        self::assertSame($uri, (string) $subject->getUri());
        self::assertTrue($subject->isLocalFile());
        self::assertSame('/foo/baz', $subject->getLocalFilePath());
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfMultiplePathsAreConfigured(): void
    {
        $uri = 'local://file?path[0]=/foo&path[1]=/baz';

        $this->expectException(Src\Exception\UrlIsInvalid::class);

        new Src\Sitemap\Sitemap(new Psr7\Uri($uri));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfPathIsEmpty(): void
    {
        $uri = 'local://file?path=';

        $this->expectException(Src\Exception\LocalFilePathIsMissingInUrl::class);

        new Src\Sitemap\Sitemap(new Psr7\Uri($uri));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenUriIsEmpty(): void
    {
        $this->expectException(Src\Exception\UrlIsEmpty::class);
        $this->expectExceptionCode(1604055264);
        $this->expectExceptionMessage('The given URL must not be empty.');

        new Src\Sitemap\Sitemap(new Psr7\Uri(''));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenUriIsNotValid(): void
    {
        $this->expectException(Src\Exception\UrlIsInvalid::class);
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
    #[Framework\Attributes\DataProvider('createFromStringReturnsSitemapObjectDataProvider')]
    public function createFromStringReturnsSitemapObject(string $sitemap, Src\Sitemap\Sitemap $expected): void
    {
        self::assertEquals($expected, Src\Sitemap\Sitemap::createFromString($sitemap));
    }

    #[Framework\Attributes\Test]
    public function isLocalFileReturnsFalseIfSitemapIsNotALocalFile(): void
    {
        $uri = new Psr7\Uri('https://foo.baz');
        $subject = new Src\Sitemap\Sitemap($uri);

        self::assertFalse($subject->isLocalFile());
        self::assertNull($subject->getLocalFilePath());
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

    /**
     * @return Generator<string, array{string, Src\Sitemap\Sitemap}>
     */
    public static function createFromStringReturnsSitemapObjectDataProvider(): Generator
    {
        $localFile = Src\Helper\FilesystemHelper::joinPathSegments(
            dirname(__DIR__).'/Fixtures/Sitemaps/valid_sitemap_1.xml',
        );

        yield 'sitemap url' => [
            'https://www.example.org/sitemap.xml',
            new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.org/sitemap.xml')),
        ];
        yield 'local sitemap file' => [
            $localFile,
            new Src\Sitemap\Sitemap(new Psr7\Uri('local://file?path='.urlencode($localFile))),
        ];
    }
}
