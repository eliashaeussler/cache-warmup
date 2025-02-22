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
use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Exception;
use Symfony\Component\Console;

use function is_string;
use function str_starts_with;
use function substr;

/**
 * ConsoleInputConfigAdapter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ConsoleInputConfigAdapter implements ConfigAdapter
{
    private const PARAMETER_MAPPING = [
        'sitemaps' => 'sitemaps',
        'urls' => '--urls',
        'excludePatterns' => '--exclude',
        'limit' => '--limit',
        'progress' => '--progress',
        'crawler' => '--crawler',
        'strategy' => '--strategy',
        'format' => '--format',
        'logFile' => '--log-file',
        'logLevel' => '--log-level',
        'allowFailures' => '--allow-failures',
        'stopOnFailure' => '--stop-on-failure',
        'repeatAfter' => '--repeat-after',
    ];

    private readonly Crawler\CrawlerFactory $crawlerFactory;
    private readonly Valinor\Mapper\TreeMapper $mapper;

    public function __construct(
        private readonly Console\Input\InputInterface $input,
    ) {
        $this->crawlerFactory = new Crawler\CrawlerFactory();
        $this->mapper = (new ConfigMapperFactory())->get();
    }

    /**
     * @throws Exception\CommandParametersAreInvalid
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     * @throws Exception\CrawlerOptionIsInvalid
     */
    public function get(): Config\CacheWarmupConfig
    {
        $nameMapping = self::PARAMETER_MAPPING;
        $nameMapping['crawlerOptions'] = '--crawler-options';

        $parameters = $this->resolveParameters();

        try {
            return $this->mapper->map(
                Config\CacheWarmupConfig::class,
                Valinor\Mapper\Source\Source::array($parameters),
            );
        } catch (Valinor\Mapper\MappingError $error) {
            throw new Exception\CommandParametersAreInvalid($error, $nameMapping);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     * @throws Exception\CrawlerOptionIsInvalid
     */
    private function resolveParameters(): array
    {
        $parameters = [];

        // Resolve command parameters
        foreach (self::PARAMETER_MAPPING as $configName => $parameterName) {
            if (str_starts_with($parameterName, '--')) {
                $parameters[$configName] = $this->input->getOption(substr($parameterName, 2));
            } else {
                $parameters[$configName] = $this->input->getArgument($parameterName);
            }
        }

        // Resolve crawler options
        if (null !== $this->input->getOption('crawler-options')) {
            /** @var string $crawlerOptions */
            $crawlerOptions = $this->input->getOption('crawler-options');
            $parameters['crawlerOptions'] = $this->crawlerFactory->parseCrawlerOptions($crawlerOptions);
        }

        // Test if given crawler is supported (throws exception if it's invalid)
        if (is_string($parameters['crawler'])) {
            $this->crawlerFactory->get($parameters['crawler']);
        }

        return $parameters;
    }
}
