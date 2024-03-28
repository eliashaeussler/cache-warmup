<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2024 Elias Häußler <elias@haeussler.dev>
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
use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Exception;

use function array_filter;
use function array_map;
use function explode;
use function getenv;
use function gettype;
use function in_array;
use function preg_replace;
use function strtoupper;
use function trim;

/**
 * EnvironmentVariablesConfigAdapter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class EnvironmentVariablesConfigAdapter implements ConfigAdapter
{
    private const ENV_VAR_PREFIX = 'CACHE_WARMUP_';
    private const BOOLEAN_VALUES = [
        'true',
        'yes',
        '1',
    ];

    private readonly Crawler\CrawlerFactory $crawlerFactory;
    private readonly Valinor\Mapper\TreeMapper $mapper;

    public function __construct()
    {
        $this->crawlerFactory = new Crawler\CrawlerFactory();
        $this->mapper = (new ConfigMapperFactory())->get();
    }

    /**
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     * @throws Exception\CrawlerOptionIsInvalid
     * @throws Exception\EnvironmentVariablesAreInvalid
     */
    public function get(): Config\CacheWarmupConfig
    {
        $config = new Config\CacheWarmupConfig();
        $configOptions = $config->toArray();

        $resolvedVariables = [];
        $nameMapping = [];

        foreach ($configOptions as $name => $defaultValue) {
            $envVarName = $this->resolveName($name);
            $envVarValue = getenv($envVarName);

            // Skip non-existent env variables
            if (false === $envVarValue) {
                continue;
            }

            $resolvedVariables[$name] = $this->processValue($envVarValue, $defaultValue, $name);
            $nameMapping[$name] = $envVarName;
        }

        try {
            return $this->mapper->map(
                Config\CacheWarmupConfig::class,
                Valinor\Mapper\Source\Source::array($resolvedVariables),
            );
        } catch (Valinor\Mapper\MappingError $error) {
            throw new Exception\EnvironmentVariablesAreInvalid($error, $nameMapping);
        }
    }

    private function resolveName(string $propertyName): string
    {
        return self::ENV_VAR_PREFIX.strtoupper((string) preg_replace('/([[:upper:]])/', '_$1', $propertyName));
    }

    /**
     * @return array<mixed>|bool|string
     *
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     * @throws Exception\CrawlerOptionIsInvalid
     */
    private function processValue(string $envVarValue, mixed $defaultValue, string $name): array|bool|string
    {
        // Test if given crawler is supported (throws exception if it's invalid)
        if ('crawler' === $name && '' !== trim($envVarValue)) {
            $this->crawlerFactory->get($envVarValue);
        }

        return match (gettype($defaultValue)) {
            'array' => match ($name) {
                'crawlerOptions' => $this->crawlerFactory->parseCrawlerOptions($envVarValue),
                default => array_filter(
                    array_map(
                        'trim',
                        explode(',', $envVarValue),
                    ),
                ),
            },
            'boolean' => in_array(trim($envVarValue), self::BOOLEAN_VALUES, true),
            default => $envVarValue,
        };
    }
}
