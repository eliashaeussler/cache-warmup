<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Crawler;

use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Result;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message;
use Symfony\Component\Console;
use Throwable;

use function assert;
use function count;

/**
 * OutputtingCrawler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class OutputtingCrawler extends ConcurrentCrawler implements VerboseCrawlerInterface
{
    protected const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% -- %url% %state%';

    protected ?Console\Output\OutputInterface $output = null;
    protected ?Console\Helper\ProgressBar $progress = null;

    public function __construct(
        array $options = [],
        ClientInterface $client = new Client(),
    ) {
        parent::__construct($options, $client);
        Console\Helper\ProgressBar::setFormatDefinition('cache-warmup', self::PROGRESS_BAR_FORMAT);
    }

    /**
     * @throws Exception\MissingArgumentException
     */
    public function crawl(array $urls): Result\CacheWarmupResult
    {
        if (null === $this->output) {
            throw Exception\MissingArgumentException::create('output');
        }

        $this->startProgressBar(count($urls));

        $result = parent::crawl($urls);

        $this->finishProgressBar();

        return $result;
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $url): Result\CrawlingResult
    {
        assert($this->progress instanceof Console\Helper\ProgressBar);

        $this->progress->setMessage((string) $url, 'url');
        $this->progress->setMessage('(<info>success</info>)', 'state');
        $this->progress->advance();
        $this->progress->display();

        return parent::onSuccess($response, $url);
    }

    public function onFailure(Throwable $exception, Message\UriInterface $url): Result\CrawlingResult
    {
        assert($this->progress instanceof Console\Helper\ProgressBar);

        $this->progress->setMessage((string) $url, 'url');
        $this->progress->setMessage('(<error>failed</error>)', 'state');
        $this->progress->advance();
        $this->progress->display();

        return parent::onFailure($exception, $url);
    }

    public function setOutput(Console\Output\OutputInterface $output): VerboseCrawlerInterface
    {
        $this->output = $output;

        return $this;
    }

    protected function startProgressBar(int $max): void
    {
        assert($this->output instanceof Console\Output\OutputInterface);

        $this->progress = new Console\Helper\ProgressBar($this->output, $max);
        $this->progress->setFormat('cache-warmup');
        $this->progress->setOverwrite(false);
        $this->progress->setMessage('', 'url');
        $this->progress->setMessage('', 'state');
    }

    protected function finishProgressBar(): void
    {
        assert($this->progress instanceof Console\Helper\ProgressBar);
        assert($this->output instanceof Console\Output\OutputInterface);

        $this->progress->finish();
        $this->output->writeln('');
    }
}
