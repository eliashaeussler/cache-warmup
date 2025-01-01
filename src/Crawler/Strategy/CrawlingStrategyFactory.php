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

namespace EliasHaeussler\CacheWarmup\Crawler\Strategy;

use EliasHaeussler\CacheWarmup\Exception;

use function array_map;
use function in_array;

/**
 * CrawlingStrategyFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CrawlingStrategyFactory
{
    private const STRATEGIES = [
        SortByChangeFrequencyStrategy::class,
        SortByLastModificationDateStrategy::class,
        SortByPriorityStrategy::class,
    ];

    /**
     * @throws Exception\CrawlingStrategyDoesNotExist
     */
    public function get(string $name): CrawlingStrategy
    {
        /** @var class-string<CrawlingStrategy> $strategy */
        foreach (self::STRATEGIES as $strategy) {
            if ($name === $strategy::getName()) {
                return new $strategy();
            }
        }

        throw new Exception\CrawlingStrategyDoesNotExist($name);
    }

    /**
     * @return list<string>
     */
    public function getAll(): array
    {
        return array_map(
            /** @param class-string<CrawlingStrategy> $strategy */
            static fn (string $strategy) => $strategy::getName(),
            self::STRATEGIES,
        );
    }

    public function has(string $name): bool
    {
        return in_array($name, $this->getAll(), true);
    }
}
