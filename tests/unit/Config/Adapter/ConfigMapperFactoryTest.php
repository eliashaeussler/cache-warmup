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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Adapter;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;

/**
 * ConfigMapperFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\ConfigMapperFactory::class)]
final class ConfigMapperFactoryTest extends Framework\TestCase
{
    private Src\Config\Adapter\ConfigMapperFactory $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Config\Adapter\ConfigMapperFactory();
    }

    #[Framework\Attributes\Test]
    public function getReturnsConfigMapper(): void
    {
        $expected = new Src\Config\CacheWarmupConfig(
            sitemaps: [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
            ],
            limit: 10,
            progress: true,
        );

        $mapper = $this->subject->get();

        self::assertEquals(
            $expected,
            $mapper->map(Src\Config\CacheWarmupConfig::class, [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                    'https://www.example.com/sitemap_en.xml',
                ],
                'limit' => 10,
                'progress' => true,
            ]),
        );
    }
}
