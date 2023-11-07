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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Exception;
use JsonException;
use Psr\Log;
use Symfony\Component\Console;

use function class_exists;
use function is_string;
use function is_subclass_of;
use function json_decode;

/**
 * CrawlerFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CrawlerFactory
{
    /**
     * @phpstan-param Log\LogLevel::* $logLevel
     */
    public function __construct(
        private readonly Console\Output\OutputInterface $output = new Console\Output\ConsoleOutput(),
        private readonly ?Log\LoggerInterface $logger = null,
        private readonly string $logLevel = Log\LogLevel::ERROR,
        private readonly bool $stopOnFailure = false,
    ) {}

    /**
     * @param class-string<CrawlerInterface> $crawler
     * @param array<string, mixed>           $options
     *
     * @throws Exception\InvalidCrawlerException
     */
    public function get(string $crawler, array $options = []): CrawlerInterface
    {
        $this->validateCrawler($crawler);

        $crawler = new $crawler();

        if ($crawler instanceof VerboseCrawlerInterface) {
            $crawler->setOutput($this->output);
        }

        if ($crawler instanceof ConfigurableCrawlerInterface) {
            $crawler->setOptions($options);
        }

        if ($crawler instanceof LoggingCrawlerInterface && null !== $this->logger) {
            $crawler->setLogger($this->logger);
            $crawler->setLogLevel($this->logLevel);
        }

        if ($crawler instanceof StoppableCrawlerInterface) {
            $crawler->stopOnFailure($this->stopOnFailure);
        }

        return $crawler;
    }

    /**
     * @param string|array<string, mixed>|null $crawlerOptions
     *
     * @return array<string, mixed>
     *
     * @throws Exception\InvalidCrawlerOptionException
     */
    public function parseCrawlerOptions(string|array|null $crawlerOptions): array
    {
        if (null === $crawlerOptions) {
            return [];
        }

        // Decode JSON array
        if (is_string($crawlerOptions)) {
            try {
                $crawlerOptions = json_decode($crawlerOptions, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw Exception\InvalidCrawlerOptionException::forInvalidType($crawlerOptions);
            }
        }

        // Handle non-array crawler options
        if (!is_array($crawlerOptions)) {
            throw Exception\InvalidCrawlerOptionException::forInvalidType($crawlerOptions);
        }

        // Handle non-associative-array crawler options
        if ($crawlerOptions !== array_filter($crawlerOptions, 'strval', ARRAY_FILTER_USE_KEY)) {
            throw Exception\InvalidCrawlerOptionException::forInvalidType($crawlerOptions);
        }

        return $crawlerOptions;
    }

    /**
     * @throws Exception\InvalidCrawlerException
     */
    private function validateCrawler(string $crawler): void
    {
        if (!class_exists($crawler)) {
            throw Exception\InvalidCrawlerException::forMissingClass($crawler);
        }

        if (!is_subclass_of($crawler, CrawlerInterface::class)) {
            throw Exception\InvalidCrawlerException::forUnsupportedClass($crawler);
        }
    }
}
