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

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use Psr\Http\Message;
use Symfony\Component\Console;
use Throwable;

/**
 * CompactProgressHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CompactProgressHandler implements ResponseHandlerInterface
{
    private const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% -- <%fail_tag%>%fail_count% %failures%</>';

    private readonly Console\Helper\ProgressBar $progressBar;
    private int $failures = 0;

    public function __construct(
        private readonly Console\Output\OutputInterface $output,
        int $max,
    ) {
        $this->progressBar = $this->createProgressBar($output, $max);
    }

    public function startProgressBar(): void
    {
        $this->progressBar->setMessage('info', 'fail_tag');
        $this->progressBar->setMessage('no', 'fail_count');
        $this->progressBar->setMessage('failures', 'failures');
        $this->progressBar->start();
    }

    public function finishProgressBar(): void
    {
        $this->progressBar->finish();
        $this->output->writeln('');
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->progressBar->advance();
        $this->progressBar->display();
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        $this->progressBar->setMessage((string) ++$this->failures, 'fail_count');

        if ($this->failures > 0) {
            $this->progressBar->setMessage('error', 'fail_tag');
        }
        if (1 === $this->failures) {
            $this->progressBar->setMessage('failure', 'failures');
        }
        if (2 === $this->failures) {
            $this->progressBar->setMessage('failures', 'failures');
        }

        $this->progressBar->advance();
        $this->progressBar->display();
    }

    private function createProgressBar(Console\Output\OutputInterface $output, int $max): Console\Helper\ProgressBar
    {
        Console\Helper\ProgressBar::setFormatDefinition('cache-warmup', self::PROGRESS_BAR_FORMAT);

        $progressBar = new Console\Helper\ProgressBar($output, $max);
        $progressBar->setFormat('cache-warmup');

        return $progressBar;
    }
}
