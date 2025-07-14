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

namespace EliasHaeussler\CacheWarmup\Tests\Log;

use EliasHaeussler\CacheWarmup as Src;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\Filesystem;

use function dirname;
use function file_get_contents;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * FileLoggerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\FileLogger::class)]
final class FileLoggerTest extends Framework\TestCase
{
    private string $logFile;
    private Src\Log\FileLogger $subject;

    protected function setUp(): void
    {
        $this->logFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('cache-warmup_tests_').DIRECTORY_SEPARATOR.'test.log';
        $this->subject = new Src\Log\FileLogger($this->logFile);
    }

    #[Framework\Attributes\Test]
    public function logCreatesLogFileIfNotExists(): void
    {
        $this->subject->log(Log\LogLevel::ERROR, 'oops, something went wrong.');

        self::assertFileExists($this->logFile);
    }

    #[Framework\Attributes\Test]
    public function logDoesNotTryToCreateLogFileMultipleTimes(): void
    {
        $this->subject->log(Log\LogLevel::ERROR, 'oops, something went wrong.');

        unlink($this->logFile);

        $this->subject->log(Log\LogLevel::ERROR, 'oops, something went wrong.');

        self::assertFileDoesNotExist($this->logFile);
    }

    #[Framework\Attributes\Test]
    public function logLogsFormattedMessageToLogFile(): void
    {
        $this->subject->log(Log\LogLevel::ERROR, 'oops, something went wrong.');

        $fileContents = file_get_contents($this->logFile);

        self::assertIsString($fileContents);
        self::assertStringContainsString('ERROR: oops, something went wrong.', $fileContents);
    }

    protected function tearDown(): void
    {
        // Run subject destruction to close log file stream
        unset($this->subject);

        $filesystem = new Filesystem\Filesystem();
        $filesystem->remove(dirname($this->logFile));
    }
}
