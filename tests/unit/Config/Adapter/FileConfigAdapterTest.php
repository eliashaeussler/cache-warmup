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
use EliasHaeussler\CacheWarmup\Tests;
use Generator;
use PHPUnit\Framework;
use Psr\Log;

use function dirname;

/**
 * FileConfigAdapterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\FileConfigAdapter::class)]
final class FileConfigAdapterTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfigFileDoesNotExist(): void
    {
        $subject = new Src\Config\Adapter\FileConfigAdapter('foo');

        $this->expectExceptionObject(new Src\Exception\ConfigFileIsMissing('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfigFileIsNotSupported(): void
    {
        $subject = new Src\Config\Adapter\FileConfigAdapter(__FILE__);

        $this->expectExceptionObject(new Src\Exception\ConfigFileIsNotSupported(__FILE__));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfYamlFileIsInvalid(): void
    {
        $file = dirname(__DIR__, 2).'/Fixtures/ConfigFiles/invalid_config.yaml';
        $subject = new Src\Config\Adapter\FileConfigAdapter($file);

        $this->expectExceptionObject(new Src\Exception\ConfigFileIsNotSupported($file));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getMapsConfigFileToCacheWarmupConfigDataProvider')]
    public function getMapsConfigFileToCacheWarmupConfig(string $file): void
    {
        $expected = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/'),
                new Src\Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#'),
                Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
            ],
            10,
            true,
            Tests\Fixtures\Classes\DummyCrawler::class,
            ['foo' => 'baz'],
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            Src\Formatter\JsonFormatter::getType(),
            'errors.log',
            Log\LogLevel::DEBUG,
            true,
            true,
            300,
        );

        $subject = new Src\Config\Adapter\FileConfigAdapter($file);

        self::assertEquals($expected, $subject->get());
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionOnInvalidConfigFile(): void
    {
        $file = dirname(__DIR__, 2).'/Fixtures/ConfigFiles/invalid_config.json';
        $subject = new Src\Config\Adapter\FileConfigAdapter($file);

        $this->expectException(Src\Exception\ConfigFileIsInvalid::class);
        $this->expectExceptionCode(1708631576);

        $subject->get();
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function getMapsConfigFileToCacheWarmupConfigDataProvider(): Generator
    {
        $fixturesPath = dirname(__DIR__, 2).'/Fixtures/ConfigFiles';

        yield 'json' => [$fixturesPath.'/valid_config.json'];
        yield 'yaml' => [$fixturesPath.'/valid_config.yaml'];
        yield 'yml' => [$fixturesPath.'/valid_config.yml'];
    }
}
