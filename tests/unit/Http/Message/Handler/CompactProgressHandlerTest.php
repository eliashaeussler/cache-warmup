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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message\Handler;

use EliasHaeussler\CacheWarmup as Src;
use Exception;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * CompactProgressHandlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Handler\CompactProgressHandler::class)]
final class CompactProgressHandlerTest extends Framework\TestCase
{
    private Console\Output\BufferedOutput $output;
    private Src\Http\Message\Handler\CompactProgressHandler $subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Src\Http\Message\Handler\CompactProgressHandler($this->output, 200);
    }

    #[Framework\Attributes\Test]
    public function onSuccessAdvancesProgressByOneStep(): void
    {
        $response = new Psr7\Response();
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();
        $this->subject->onSuccess($response, $uri);

        self::assertSame('.', $this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function onSuccessPrintsCurrentStateOnLineBreak(): void
    {
        $response = new Psr7\Response();
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();

        for ($i = 0; $i < 100; ++$i) {
            $this->subject->onSuccess($response, $uri);
        }

        self::assertStringContainsString('63 / 200 ( 32%)', $this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function onSuccessPrintsFinalStateOnFinishedCrawling(): void
    {
        $response = new Psr7\Response();
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();

        for ($i = 0; $i < 200; ++$i) {
            $this->subject->onSuccess($response, $uri);
        }

        self::assertStringContainsString('200 / 200 (100%)', $this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function onFailureAdvancesProgressByOneStep(): void
    {
        $exception = new Exception('foo');
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();
        $this->subject->onFailure($exception, $uri);
        $this->subject->onFailure($exception, $uri);

        self::assertSame('FF', $this->output->fetch());
    }
}
