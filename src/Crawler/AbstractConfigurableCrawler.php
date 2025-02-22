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

namespace EliasHaeussler\CacheWarmup\Crawler;

use Symfony\Component\OptionsResolver;

/**
 * AbstractConfigurableCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @template TOptions of array<string, mixed>
 */
abstract class AbstractConfigurableCrawler implements ConfigurableCrawler
{
    protected OptionsResolver\OptionsResolver $optionsResolver;

    /**
     * @var TOptions
     */
    protected array $options = [];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->optionsResolver = new OptionsResolver\OptionsResolver();
        $this->configureOptions($this->optionsResolver);
        $this->setOptions($options);
    }

    abstract protected function configureOptions(OptionsResolver\OptionsResolver $optionsResolver): void;

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $this->optionsResolver->resolve($options);
    }
}
