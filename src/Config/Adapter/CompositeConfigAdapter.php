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

namespace EliasHaeussler\CacheWarmup\Config\Adapter;

use EliasHaeussler\CacheWarmup\Config;

use function count;

/**
 * CompositeConfigAdapter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CompositeConfigAdapter implements ConfigAdapter
{
    /**
     * @param list<ConfigAdapter> $adapters
     */
    public function __construct(
        private readonly array $adapters,
    ) {}

    public function get(): Config\CacheWarmupConfig
    {
        $config = new Config\CacheWarmupConfig();

        // Avoid config merge if only one adapter is configured
        if (1 === count($this->adapters)) {
            return $this->adapters[0]->get();
        }

        foreach ($this->adapters as $adapter) {
            $config->merge($adapter->get());
        }

        return $config;
    }
}
