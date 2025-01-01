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

namespace EliasHaeussler\CacheWarmup\Http\Message\Handler;

use Psr\Http\Message;
use Symfony\Component\Console;
use Throwable;

use function min;
use function round;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;

/**
 * CompactProgressHandler.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CompactProgressHandler implements ResponseHandler
{
    private const MAX_LINE_LENGTH = 80;

    private readonly int $maxColumns;
    private int $columns = 0;
    private int $current = 0;

    public function __construct(
        private readonly Console\Output\OutputInterface $output,
        private readonly int $max,
    ) {
        $this->maxColumns = $this->calculateMaxColumns();
    }

    public function startProgressBar(): void
    {
        $this->reset();
    }

    public function finishProgressBar(): void
    {
        $this->reset();
    }

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->advance('.');
    }

    public function onFailure(Throwable $exception, Message\UriInterface $uri): void
    {
        $this->advance('<error>F</error>');
    }

    private function advance(string $output): void
    {
        ++$this->columns;
        ++$this->current;

        if ($this->columns >= $this->maxColumns || $this->current === $this->max) {
            $this->output->writeln($output.$this->renderCurrentState());
            $this->columns = 0;
        } else {
            $this->output->write($output);
        }
    }

    private function renderCurrentState(): string
    {
        $percent = round(($this->current / $this->max) * 100);

        if ($this->max === $this->current) {
            $fill = str_repeat(' ', $this->maxColumns - $this->columns);
        } else {
            $fill = '';
        }

        return sprintf(
            '%s %s / %d (%s%%)',
            $fill,
            str_pad((string) $this->current, strlen((string) $this->max), ' ', STR_PAD_LEFT),
            $this->max,
            str_pad((string) $percent, 3, ' ', STR_PAD_LEFT),
        );
    }

    private function calculateMaxColumns(): int
    {
        // current / max (...%)
        $stateLength = 2 * strlen((string) $this->max) + 11;
        $lineLength = min(
            (new Console\Terminal())->getWidth(),
            self::MAX_LINE_LENGTH,
        );

        return $lineLength - $stateLength;
    }

    private function reset(): void
    {
        $this->columns = 0;
        $this->current = 0;
    }
}
