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

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\DependencyInjection;
use Symfony\Component\EventDispatcher;

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
    /**
     * @phpstan-param Log\LogLevel::* $logLevel
     */
    public function __construct(
        private readonly Console\Output\OutputInterface $output = new Console\Output\ConsoleOutput(),
        private readonly ?Log\LoggerInterface $logger = null,
        private readonly string $logLevel = Log\LogLevel::ERROR,
        private readonly bool $stopOnFailure = false,
        private readonly EventDispatcherInterface $eventDispatcher = new EventDispatcher\EventDispatcher(),
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
        $this->validate($crawlerClass);

        $container = $this->buildLimitedContainerForCrawler($crawlerClass);
        /** @var Crawler $crawler */
        $crawler = $container->get($crawlerClass);

        if ($crawler instanceof VerboseCrawler) {
            $crawler->setOutput($this->output);
        }

        if ($crawler instanceof ConfigurableCrawler) {
            $crawler->setOptions($options);
        }

        if ($crawler instanceof LoggingCrawler && null !== $this->logger) {
            $crawler->setLogger($this->logger);
            $crawler->setLogLevel($this->logLevel);
        }

        if ($crawler instanceof StoppableCrawler) {
            $crawler->stopOnFailure($this->stopOnFailure);
        }

        return $crawler;
    }

    /**
     * @throws Exception\CrawlerDoesNotExist
     * @throws Exception\CrawlerIsInvalid
     */
    public function validate(string $crawlerClass): void
    {
        if (!class_exists($crawlerClass)) {
            throw new Exception\CrawlerDoesNotExist($crawlerClass);
        }

        if (!is_subclass_of($crawlerClass, Crawler::class)) {
            throw new Exception\CrawlerIsInvalid($crawlerClass);
        }
    }

    /**
     * @param class-string<Crawler> $crawlerClass
     */
    private function buildLimitedContainerForCrawler(string $crawlerClass): DependencyInjection\ContainerInterface
    {
        $container = new DependencyInjection\ContainerBuilder();

        // Register crawler as public service
        $container->register($crawlerClass)
            ->setPublic(true)
            ->setAutowired(true)
        ;

        // Register output as runtime service
        $container->register($this->output::class)->setSynthetic(true);
        $container->setAlias(Console\Output\OutputInterface::class, $this->output::class);

        // Register logger as runtime service
        if (null !== $this->logger) {
            $container->register($this->logger::class)->setSynthetic(true);
            $container->setAlias(Log\LoggerInterface::class, $this->logger::class);
        }

        // Register event dispatcher as runtime service
        $container->register(EventDispatcher\EventDispatcher::class)->setSynthetic(true);
        $container->setAlias(EventDispatcherInterface::class, EventDispatcher\EventDispatcher::class);

        // Compile container
        $container->compile();

        // Inject runtime services
        $container->set($this->output::class, $this->output);
        $container->set(EventDispatcher\EventDispatcher::class, $this->eventDispatcher);

        // Inject logger runtime service
        if (null !== $this->logger) {
            $container->set($this->logger::class, $this->logger);
        }

        return $container;
    }
}
