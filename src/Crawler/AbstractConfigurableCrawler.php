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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Exception\InvalidCrawlerOptionException;

use function array_diff_key;

/**
 * AbstractConfigurableCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @template TOptions of array
 */
abstract class AbstractConfigurableCrawler implements ConfigurableCrawlerInterface
{
    /**
     * @var TOptions
     */
    protected static $defaultOptions = [];

    /**
     * @var TOptions
     */
    protected $options = [];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $invalidOptions = array_diff_key($options, static::$defaultOptions);

        if ([] !== $invalidOptions) {
            throw InvalidCrawlerOptionException::createForAll($this, array_keys($invalidOptions));
        }

        $this->options = array_merge(
            static::$defaultOptions,
            array_intersect_key($options, static::$defaultOptions)
        );
    }
}
