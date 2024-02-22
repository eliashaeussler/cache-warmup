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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Adapter;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

use function dirname;

/**
 * PhpConfigAdapterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\PhpConfigAdapter::class)]
final class PhpConfigAdapterTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfigFileDoesNotExist(): void
    {
        $subject = new Src\Config\Adapter\PhpConfigAdapter('foo');

        $this->expectExceptionObject(Src\Exception\MissingConfigFileException::create('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfigFileIsNotSupported(): void
    {
        $file = dirname(__DIR__, 2).'/Fixtures/ConfigFiles/valid_config.json';
        $subject = new Src\Config\Adapter\PhpConfigAdapter($file);

        $this->expectExceptionObject(Src\Exception\UnsupportedConfigFileException::create($file));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfigFileDoesNotReturnCallable(): void
    {
        $file = dirname(__DIR__, 2).'/Fixtures/ConfigFiles/invalid_config.php';
        $subject = new Src\Config\Adapter\PhpConfigAdapter($file);

        $this->expectExceptionObject(Src\Exception\UnsupportedConfigFileException::create($file));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getCallsProvidedClosureWithCacheWarmupConfig(): void
    {
        $file = dirname(__DIR__, 2).'/Fixtures/ConfigFiles/valid_config.php';
        $subject = new Src\Config\Adapter\PhpConfigAdapter($file);

        $expected = new Src\Config\CacheWarmupConfig(
            sitemaps: [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
            ],
            limit: 10,
            progress: true,
        );

        self::assertEquals($expected, $subject->get());
    }
}
