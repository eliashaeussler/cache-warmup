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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Exception;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * OutputtingCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class OutputtingCrawlerTest extends Framework\TestCase
{
    private Console\Output\BufferedOutput $output;
    private Crawler\OutputtingCrawler$subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Crawler\OutputtingCrawler();
        $this->subject->setOutput($this->output);
    }

    /**
     * @test
     */
    public function crawlThrowsExceptionIfOutputIsNotSet(): void
    {
        $subject = new Crawler\OutputtingCrawler();

        $this->expectException(Exception\MissingArgumentException::class);
        $this->expectExceptionCode(1619635638);

        $subject->crawl([]);
    }

    /**
     * @test
     */
    public function crawlCreatesProgressBarAndWritesCrawlingStateToOutput(): void
    {
        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');
        $this->subject->crawl([$uri1, $uri2]);

        $output = $this->output->fetch();
        self::assertMatchesRegularExpression(
            sprintf('#^\s*\d/\d [^\s]+\s+\d+%% -- %s \((success|failed)\)$#m', preg_quote((string) $uri1)),
            $output
        );
        self::assertMatchesRegularExpression(
            sprintf('#^\s*\d/\d [^\s]+\s+\d+%% -- %s \(failed\)$#m', preg_quote((string) $uri2)),
            $output
        );
    }
}
