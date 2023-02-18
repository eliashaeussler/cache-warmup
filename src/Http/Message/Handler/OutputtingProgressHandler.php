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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use Psr\Http\Message;
use Symfony\Component\Console;
use Throwable;

/**
 * OutputtingProgressHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class OutputtingProgressHandler implements ResponseHandlerInterface
{
    private const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% -- %url% %state%';

    private Console\Helper\ProgressBar $progressBar;

    public function __construct(
        Console\Output\OutputInterface $output,
        int $max,
    ) {
        $this->progressBar = $this->createProgressBar($output, $max);
    }

    public function startProgressBar(): void
    {
        $this->progressBar->setMessage('', 'url');
        $this->progressBar->setMessage('', 'state');
        $this->progressBar->start();
    }

    public function finishProgressBar(): void
    {
        $this->progressBar->finish();
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->progressBar->setMessage((string) $uri, 'url');
        $this->progressBar->setMessage('(<info>success</info>)', 'state');
        $this->progressBar->advance();
        $this->progressBar->display();
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        $this->progressBar->setMessage((string) $uri, 'url');
        $this->progressBar->setMessage('(<error>failed</error>)', 'state');
        $this->progressBar->advance();
        $this->progressBar->display();
    }

    private function createProgressBar(Console\Output\OutputInterface $output, int $max): Console\Helper\ProgressBar
    {
        Console\Helper\ProgressBar::setFormatDefinition('cache-warmup', self::PROGRESS_BAR_FORMAT);

        $progressBar = new Console\Helper\ProgressBar($output, $max);

        $progressBar->setFormat('cache-warmup');
        $progressBar->setOverwrite(false);

        return $progressBar;
    }
}
