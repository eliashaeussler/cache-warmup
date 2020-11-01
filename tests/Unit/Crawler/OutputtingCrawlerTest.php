<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * OutputtingCrawlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class OutputtingCrawlerTest extends TestCase
{
    /**
     * @var BufferedOutput
     */
    protected $output;

    /**
     * @var OutputtingCrawler
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->subject = new OutputtingCrawler($this->output);
    }

    /**
     * @test
     */
    public function crawlCreatesProgressBarAndWritesCrawlingStateToOutput(): void
    {
        $uri1 = new Uri('https://www.example.org');
        $uri2 = new Uri('https://www.foo.baz');
        $this->subject->crawl([$uri1, $uri2]);

        $output = $this->output->fetch();
        static::assertMatchesRegularExpression(
            sprintf('#^\s*\d/\d [^\s]+\s+\d+%% -- %s \((success|failed)\)$#m', preg_quote((string)$uri1)),
            $output
        );
        static::assertMatchesRegularExpression(
            sprintf('#^\s*\d/\d [^\s]+\s+\d+%% -- %s \(failed\)$#m', preg_quote((string)$uri2)),
            $output
        );
    }
}
