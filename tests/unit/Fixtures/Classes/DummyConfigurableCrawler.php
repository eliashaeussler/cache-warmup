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

namespace EliasHaeussler\CacheWarmup\Tests\Fixtures\Classes;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Result;
use Symfony\Component\OptionsResolver;

/**
 * DummyConfigurableCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 *
 * @extends Crawler\AbstractConfigurableCrawler<array{foo: string, bar: int}>
 */
final class DummyConfigurableCrawler extends Crawler\AbstractConfigurableCrawler
{
    public function crawl(array $urls): Result\CacheWarmupResult
    {
        return new Result\CacheWarmupResult();
    }

    /**
     * @return array{foo: string, bar: int}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    protected function configureOptions(OptionsResolver\OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('foo')
            ->allowedTypes('string')
            ->default('hello world')
        ;

        $optionsResolver->define('bar')
            ->allowedTypes('int')
            ->default(42)
        ;
    }
}
