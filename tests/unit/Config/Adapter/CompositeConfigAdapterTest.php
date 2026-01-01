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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Adapter;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use PHPUnit\Framework;

/**
 * CompositeConfigAdapterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\CompositeConfigAdapter::class)]
final class CompositeConfigAdapterTest extends Framework\TestCase
{
    private Tests\Fixtures\Classes\DummyConfigAdapter $adapter1;
    private Tests\Fixtures\Classes\DummyConfigAdapter $adapter2;
    private Src\Config\Adapter\CompositeConfigAdapter $subject;

    public function setUp(): void
    {
        $this->adapter1 = new Tests\Fixtures\Classes\DummyConfigAdapter();
        $this->adapter2 = new Tests\Fixtures\Classes\DummyConfigAdapter();
        $this->subject = new Src\Config\Adapter\CompositeConfigAdapter([
            $this->adapter1,
            $this->adapter2,
        ]);
    }

    #[Framework\Attributes\Test]
    public function getReturnsConfigFromFirstAdapterIfOnlyOneAdapterIsConfigured(): void
    {
        $subject = new Src\Config\Adapter\CompositeConfigAdapter([$this->adapter1]);

        self::assertSame($this->adapter1->config, $subject->get());
    }

    #[Framework\Attributes\Test]
    public function getMergesConfigurationFromAdapters(): void
    {
        $this->adapter1->config->addSitemap('https://www.example.com/sitemap.xml');
        $this->adapter1->config->setLimit(10);

        $this->adapter2->config->addSitemap('https://www.example.com/sitemap_en.xml');
        $this->adapter2->config->enableProgressBar();

        $expected = new Src\Config\CacheWarmupConfig(
            sitemaps: [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
            ],
            limit: 10,
            progress: true,
        );

        self::assertEquals($expected, $this->subject->get());
    }
}
