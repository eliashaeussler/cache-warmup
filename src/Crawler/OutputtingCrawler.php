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

use EliasHaeussler\CacheWarmup\Http;
use EliasHaeussler\CacheWarmup\Result;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise;
use Psr\Log;
use Symfony\Component\Console;

use function count;

/**
 * OutputtingCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @extends AbstractConfigurableCrawler<array{
 *     concurrency: int,
 *     request_method: string,
 *     request_headers: array<string, string>,
 *     request_options: array<string, mixed>,
 *     client_config: array<string, mixed>,
 * }>
 */
final class OutputtingCrawler extends AbstractConfigurableCrawler implements LoggingCrawlerInterface, StoppableCrawlerInterface, VerboseCrawlerInterface
{
    use ConcurrentCrawlerTrait;

    protected static array $defaultOptions = [
        'concurrency' => 5,
        'request_method' => 'HEAD',
        'request_headers' => [],
        'request_options' => [],
        'client_config' => [],
    ];

    private readonly ClientInterface $client;
    private Console\Output\OutputInterface $output;
    private ?Log\LoggerInterface $logger = null;

    /**
     * @phpstan-var Log\LogLevel::*
     */
    private string $logLevel = Log\LogLevel::ERROR;
    private bool $stopOnFailure = false;

    public function __construct(
        array $options = [],
        ClientInterface $client = null,
    ) {
        parent::__construct($options);

        $this->client = $client ?? new Client($this->options['client_config']);
        $this->output = new Console\Output\ConsoleOutput();
    }

    public function crawl(array $urls): Result\CacheWarmupResult
    {
        $numberOfUrls = count($urls);
        $resultHandler = new Http\Message\Handler\ResultCollectorHandler();
        $result = $resultHandler->getResult();

        // Create progress response handler (depends on the available output)
        if ($this->output instanceof Console\Output\ConsoleOutputInterface && $this->output->isVerbose()) {
            $progressBarHandler = new Http\Message\Handler\VerboseProgressHandler($this->output, $numberOfUrls);
        } else {
            $progressBarHandler = new Http\Message\Handler\CompactProgressHandler($this->output, $numberOfUrls);
        }

        // Define common handlers
        $handlers = [$resultHandler, $progressBarHandler];

        // Add log handler
        if (null !== $this->logger) {
            $logHandler = new Http\Message\Handler\LogHandler($this->logger, $this->logLevel);
            $handlers[] = $logHandler;
        }

        // Create request pool
        $pool = $this->createPool($urls, $this->client, $handlers, $this->stopOnFailure);

        // Start crawling
        try {
            $progressBarHandler->startProgressBar();
            $pool->promise()->wait();
            $progressBarHandler->finishProgressBar();
        } catch (Promise\CancellationException) {
            $result->setCancelled(true);
        }

        return $result;
    }

    public function setOutput(Console\Output\OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function setLogger(Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function stopOnFailure(bool $stopOnFailure = true): void
    {
        $this->stopOnFailure = $stopOnFailure;
    }
}
