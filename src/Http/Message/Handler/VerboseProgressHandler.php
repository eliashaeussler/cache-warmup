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

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use EliasHaeussler\CacheWarmup\Helper;
use Psr\Http\Message;
use Symfony\Component\Console;
use Throwable;

use function sprintf;

/**
 * VerboseProgressHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class VerboseProgressHandler implements ResponseHandler
{
    private readonly Console\Output\ConsoleSectionOutput $logSection;
    private readonly Console\Output\ConsoleSectionOutput $progressBarSection;
    private readonly Console\Helper\ProgressBar $progressBar;
    private bool $hasFailures = false;

    public function __construct(Console\Output\ConsoleOutputInterface $output, int $max)
    {
        Helper\ConsoleHelper::registerAdditionalConsoleOutputStyles($output->getFormatter());

        $this->logSection = $output->section();
        $this->progressBarSection = $output->section();
        $this->progressBar = $this->createProgressBar($max);
    }

    public function startProgressBar(): void
    {
        $this->progressBarSection->writeln('');
        $this->progressBar->start();
        $this->hasFailures = false;
    }

    public function finishProgressBar(): void
    {
        $this->progressBar->finish();
        $this->progressBarSection->clear();
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->logSection->writeln(sprintf('<success> DONE </success> <href=%s>%s</>', $uri, $uri));
        $this->progressBar->advance();
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        $this->logSection->writeln(sprintf('<failure> FAIL </failure> <href=%s>%s</>', $uri, $uri));
        $this->progressBar->advance();

        if (!$this->hasFailures) {
            $this->progressBar->display();
            $this->hasFailures = true;
        }
    }

    private function createProgressBar(int $max): Console\Helper\ProgressBar
    {
        $symfonyStyle = new Console\Style\SymfonyStyle(
            new Console\Input\StringInput(''),
            $this->progressBarSection,
        );

        return $symfonyStyle->createProgressBar($max);
    }
}
