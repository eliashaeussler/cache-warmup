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

namespace EliasHaeussler\CacheWarmup\Tests\Helper;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

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
        $expected = dirname(__DIR__).'/foo/baz';

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
}
