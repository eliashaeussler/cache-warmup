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
final readonly class CrawlingStrategyFactory
{
    /**
     * @param list<class-string<CrawlingStrategy>> $strategies
     */
    public function __construct(
        private array $strategies = [
            SortByChangeFrequencyStrategy::class,
            SortByLastModificationDateStrategy::class,
            SortByPriorityStrategy::class,
        ],
    ) {}

    /**
     * @throws Exception\CrawlingStrategyDoesNotExist
     */
    public function get(string $name): CrawlingStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($name === $strategy::getName()) {
                return new $strategy();
            }
        }

        throw new Exception\CrawlingStrategyDoesNotExist($name);
    }

    /**
     * @return list<non-empty-string>
     */
    public function getAll(): array
    {
        return array_map(
            static fn (string $strategy) => $strategy::getName(),
            $this->strategies,
        );
    }

    public function has(string $name): bool
    {
        return in_array($name, $this->getAll(), true);
    }
}
