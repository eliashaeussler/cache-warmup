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
        $this->subject = new Src\Http\Message\Handler\CompactProgressHandler($this->output, 10);
    }

    #[Framework\Attributes\Test]
    public function startProgressBarStartsProgressBar(): void
    {
        $this->subject->startProgressBar();

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertMatchesRegularExpression('#^\s*0/10 \S+\s+0% -- no failures#m', $output);
    }

    #[Framework\Attributes\Test]
    public function finishProgressBarFinishesProgressBar(): void
    {
        $this->subject->startProgressBar();
        $this->subject->finishProgressBar();

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertMatchesRegularExpression('#^\s*0/10 \S+\s+0% -- no failures#m', $output);
        self::assertMatchesRegularExpression('#^\s*10/10 \S+\s+100% -- no failures#m', $output);
    }

    #[Framework\Attributes\Test]
    public function onSuccessPrintsSuccessfulUrlAndAdvancesProgressBarByOneStep(): void
    {
        $response = new Psr7\Response();
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();
        $this->subject->onSuccess($response, $uri);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertMatchesRegularExpression('#^\s*1/10 \S+\s+10% -- no failures#m', $output);
    }

    #[Framework\Attributes\Test]
    public function onFailurePrintsFailedUrlAndAdvancesProgressBarByOneStep(): void
    {
        $exception = new Exception('foo');
        $uri = new Psr7\Uri('https://www.example.com');

        $this->subject->startProgressBar();
        $this->subject->onFailure($exception, $uri);
        $this->subject->onFailure($exception, $uri);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertMatchesRegularExpression('#^\s*1/10 \S+\s+10% -- 1 failure#m', $output);
        self::assertMatchesRegularExpression('#^\s*2/10 \S+\s+20% -- 2 failures#m', $output);
    }
}
