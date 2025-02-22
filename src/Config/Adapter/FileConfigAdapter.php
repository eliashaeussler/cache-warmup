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

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup\Config;
use EliasHaeussler\CacheWarmup\Exception;
use SplFileObject;
use Symfony\Component\Filesystem;
use Symfony\Component\Yaml;

use function file_exists;
use function is_iterable;

/**
 * FileConfigAdapter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class FileConfigAdapter implements ConfigAdapter
{
    private readonly Valinor\Mapper\TreeMapper $mapper;

    public function __construct(
        private readonly string $file,
    ) {
        $this->mapper = (new ConfigMapperFactory())->get();
    }

    /**
     * @throws Exception\ConfigFileIsInvalid
     * @throws Exception\ConfigFileIsMissing
     * @throws Exception\ConfigFileIsNotSupported
     */
    public function get(): Config\CacheWarmupConfig
    {
        if (!file_exists($this->file)) {
            throw new Exception\ConfigFileIsMissing($this->file);
        }

        $source = match (Filesystem\Path::getExtension($this->file, true)) {
            'json' => Valinor\Mapper\Source\Source::file(new SplFileObject($this->file)),
            'yaml', 'yml' => $this->parseYamlSource(),
            default => throw new Exception\ConfigFileIsNotSupported($this->file),
        };

        try {
            return $this->mapper->map(Config\CacheWarmupConfig::class, $source);
        } catch (Valinor\Mapper\MappingError $error) {
            throw new Exception\ConfigFileIsInvalid($this->file, $error);
        }
    }

    /**
     * @throws Exception\ConfigFileIsNotSupported
     */
    private function parseYamlSource(): Valinor\Mapper\Source\Source
    {
        $yaml = Yaml\Yaml::parseFile($this->file);

        if (!is_iterable($yaml)) {
            throw new Exception\ConfigFileIsNotSupported($this->file);
        }

        return Valinor\Mapper\Source\Source::iterable($yaml);
    }
}
