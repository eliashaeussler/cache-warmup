<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests;

use RuntimeException;
use Symfony\Component\Console;

use function fopen;
use function fseek;
use function stream_get_contents;

/**
 * BufferedConsoleOutput.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class BufferedConsoleOutput extends Console\Output\StreamOutput implements Console\Output\ConsoleOutputInterface
{
    /**
     * @var resource
     */
    private $stream;
    private Console\Output\OutputInterface $stderr;

    public function __construct()
    {
        $this->stream = $this->createStream();
        $this->stderr = new Console\Output\StreamOutput($this->createStream());

        parent::__construct($this->stream, decorated: false);
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function fetch(): string
    {
        fseek($this->stream, 0);

        return stream_get_contents($this->stream);
    }

    public function getErrorOutput(): Console\Output\OutputInterface
    {
        return $this->stderr;
    }

    public function setErrorOutput(Console\Output\OutputInterface $error): void
    {
        $this->stderr = $error;
    }

    public function section(): Console\Output\ConsoleSectionOutput
    {
        $sections = [];

        return new Console\Output\ConsoleSectionOutput(
            $this->stream,
            $sections,
            $this->getVerbosity(),
            $this->isDecorated(),
            $this->getFormatter(),
        );
    }

    /**
     * @return resource
     */
    private function createStream()
    {
        $stream = fopen('php://temp', 'w+');

        if (false === $stream) {
            throw new RuntimeException('No output stream is available.', 1676746492);
        }

        return $stream;
    }
}
