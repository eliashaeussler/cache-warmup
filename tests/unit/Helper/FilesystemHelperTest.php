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

namespace EliasHaeussler\CacheWarmup\Tests\Helper;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use PHPUnit\Framework;

use function implode;

/**
 * FilesystemHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\FilesystemHelper::class)]
final class FilesystemHelperTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function resolveRelativePathReturnsAbsolutePathIfGivenPathIsAbsolute(): void
    {
        self::assertSame('/foo', Src\Helper\FilesystemHelper::resolveRelativePath('/foo'));
    }

    #[Framework\Attributes\Test]
    public function resolveRelativePathPrependsProjectRootPath(): void
    {
        $currentWorkingDirectory = getcwd();
        $projectRootPath = __DIR__.'/..';
        $expected = Src\Helper\FilesystemHelper::joinPathSegments(dirname(__DIR__).'/foo/baz');

        self::assertNotFalse($currentWorkingDirectory, 'Unable to get current working directory.');

        chdir($projectRootPath);

        self::assertSame($expected, Src\Helper\FilesystemHelper::resolveRelativePath('foo/baz'));

        chdir($currentWorkingDirectory);
    }

    #[Framework\Attributes\Test]
    public function getWorkingDirectoryReturnsCurrentWorkingDirectory(): void
    {
        $cwd = dirname(__DIR__, 3);

        self::assertSame($cwd, Src\Helper\FilesystemHelper::getWorkingDirectory());

        chdir(__DIR__);

        self::assertSame(__DIR__, Src\Helper\FilesystemHelper::getWorkingDirectory());

        chdir($cwd);
    }

    /**
     * @param list<string> $pathSegments
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('joinPathSegmentsMergesGivenPathSegmentsWithGlobalDirectorySeparatorDataProvider')]
    public function joinPathSegmentsMergesGivenPathSegmentsWithGlobalDirectorySeparator(
        array $pathSegments,
        string $expected,
    ): void {
        self::assertSame($expected, Src\Helper\FilesystemHelper::joinPathSegments(...$pathSegments));
    }

    /**
     * @return Generator<string, array{list<string>, string}>
     */
    public static function joinPathSegmentsMergesGivenPathSegmentsWithGlobalDirectorySeparatorDataProvider(): Generator
    {
        $expected = DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['foo', 'baz']);

        yield 'no path segments' => [[], ''];
        yield 'single path segment' => [['/foo/baz'], $expected];
        yield 'multiple path segments' => [['/foo', 'baz'], $expected];
        yield 'with empty path segments' => [['/foo', '', 'baz', ''], $expected];
    }
}
