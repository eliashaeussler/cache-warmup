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
use EliasHaeussler\CacheWarmup\Exception;
use Symfony\Component\Filesystem;

use function file_exists;
use function is_callable;

/**
 * PhpConfigAdapter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class PhpConfigAdapter implements ConfigAdapter
{
    public function __construct(
        private string $file,
    ) {}

    /**
     * @throws Exception\ConfigFileIsMissing
     * @throws Exception\ConfigFileIsNotSupported
     */
    public function get(): Config\CacheWarmupConfig
    {
        if (!file_exists($this->file)) {
            throw new Exception\ConfigFileIsMissing($this->file);
        }

        if ('php' !== Filesystem\Path::getExtension($this->file, true)) {
            throw new Exception\ConfigFileIsNotSupported($this->file);
        }

        $closure = require $this->file;

        if (!is_callable($closure)) {
            throw new Exception\ConfigFileIsNotSupported($this->file);
        }

        $config = new Config\CacheWarmupConfig();

        // We use static method to decouple closure from adapter object context
        return self::fetchConfig($closure, $config);
    }

    private static function fetchConfig(callable $closure, Config\CacheWarmupConfig $config): Config\CacheWarmupConfig
    {
        $resolvedConfig = $closure($config);

        if (!($resolvedConfig instanceof Config\CacheWarmupConfig)) {
            $resolvedConfig = $config;
        }

        return $resolvedConfig;
    }
}
