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

namespace EliasHaeussler\CacheWarmup\Log;

use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Helper;
use Psr\Log;
use Stringable;

use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function fwrite;
use function is_dir;
use function is_resource;
use function mkdir;
use function touch;

/**
 * FileLogger.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class FileLogger extends Log\AbstractLogger
{
    private readonly string $file;
    private bool $fileCreated = false;

    /**
     * @var resource|null
     */
    private $stream;

    public function __construct(
        string $file,
        private readonly Formatter\LogFormatter $formatter = new Formatter\CompactLogFormatter(),
    ) {
        $this->file = Helper\FilesystemHelper::resolveRelativePath($file);
    }

    /**
     * @phpstan-param Log\LogLevel::* $level
     *
     * @param array<string, mixed> $context
     *
     * @throws Exception\FilesystemFailureException
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (!is_resource($this->stream)) {
            $this->stream = $this->openStream();
        }

        $formatted = $this->formatter->format($level, $message, $context);

        fwrite($this->stream, $formatted.PHP_EOL);
    }

    /**
     * @return resource
     *
     * @throws Exception\FilesystemFailureException
     */
    private function openStream()
    {
        $this->createLogFileIfNotExists();

        $stream = fopen($this->file, 'a');

        if (!is_resource($stream)) {
            throw Exception\FilesystemFailureException::forUnexpectedFileStreamResult($this->file);
        }

        return $stream;
    }

    private function closeStream(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->fileCreated = false;
        $this->stream = null;
    }

    private function createLogFileIfNotExists(): void
    {
        // Don't try to create file multiple times
        if ($this->fileCreated) {
            return;
        }

        // Assure directory exists
        if (!is_dir(dirname($this->file))) {
            mkdir(dirname($this->file), recursive: true);
        }

        // Create file if not exists
        if (!file_exists($this->file)) {
            touch($this->file);
        }

        // Set flag to not create file multiple times
        $this->fileCreated = true;
    }

    public function __destruct()
    {
        $this->closeStream();
    }
}
