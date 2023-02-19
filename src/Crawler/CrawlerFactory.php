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
use Symfony\Component\Console;

use function class_exists;
use function is_subclass_of;

/**
 * CrawlerFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CrawlerFactory
{
    public function __construct(
        private readonly Console\Output\OutputInterface $output = new Console\Output\ConsoleOutput(),
    ) {
    }

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

        return $crawler;
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
