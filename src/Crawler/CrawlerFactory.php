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

use EliasHaeussler\CacheWarmup\DependencyInjection;
use EliasHaeussler\CacheWarmup\Event;
use EliasHaeussler\CacheWarmup\Exception;
use Psr\EventDispatcher;
use Psr\Log;
use Symfony\Component\Console;

use function class_exists;
use function is_subclass_of;

/**
 * CrawlerFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class CrawlerFactory
{
    /**
     * @phpstan-param Log\LogLevel::* $logLevel
     */
    public function __construct(
        private DependencyInjection\ContainerFactory $containerFactory,
        private string $logLevel = Log\LogLevel::ERROR,
        private bool $stopOnFailure = false,
    ) {}

    /**
     * @param class-string<Crawler> $crawlerClass
     * @param array<string, mixed>  $options
     *
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     */
    public function get(string $crawlerClass, array $options = []): Crawler
    {
        self::validate($crawlerClass);

        $container = $this->containerFactory->buildFor($crawlerClass);
        /** @var Crawler $crawler */
        $crawler = $container->get($crawlerClass);

        if ($crawler instanceof VerboseCrawler) {
            $crawler->setOutput($container->get(Console\Output\OutputInterface::class));
        }

        if ($crawler instanceof ConfigurableCrawler) {
            $crawler->setOptions($options);
        }

        /* @phpstan-ignore booleanAnd.rightAlwaysTrue */
        if ($crawler instanceof LoggingCrawler && $container->has(Log\LoggerInterface::class)) {
            $crawler->setLogger($container->get(Log\LoggerInterface::class));
            $crawler->setLogLevel($this->logLevel);
        }

        if ($crawler instanceof StoppableCrawler) {
            $crawler->stopOnFailure($this->stopOnFailure);
        }

        $eventDispatcher = $container->get(EventDispatcher\EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new Event\Crawler\CrawlerConstructed($crawler));

        return $crawler;
    }

    /**
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     */
    public static function validate(string $crawlerClass): void
    {
        if (!class_exists($crawlerClass)) {
            throw new Exception\CrawlerDoesNotExist($crawlerClass);
        }

        if (!is_subclass_of($crawlerClass, Crawler::class)) {
            throw new Exception\CrawlerIsInvalid($crawlerClass);
        }
    }
}
