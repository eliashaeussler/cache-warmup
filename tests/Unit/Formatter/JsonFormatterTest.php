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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Formatter;

use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * JsonFormatterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class JsonFormatterTest extends Framework\TestCase
{
    private Console\Output\BufferedOutput $output;
    private Src\Formatter\JsonFormatter $subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Src\Formatter\JsonFormatter(
            new Console\Style\SymfonyStyle(new Console\Input\StringInput(''), $this->output),
        );
    }

    #[Framework\Attributes\Test]
    public function formatParserResultDoesNotAddSuccessfulResultIfOutputIsNotVeryVerbose(): void
    {
        $successful = new Src\Result\ParserResult([new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com'))]);
        $failed = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed);

        self::assertSame([], $this->subject->getJson());
    }

    #[Framework\Attributes\Test]
    public function formatParserResultAddsSuccessfulResultIfOutputIsVeryVerbose(): void
    {
        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE);

        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult(
            [new Src\Sitemap\Sitemap(new Psr7\Uri($url))],
            [new Src\Sitemap\Url($url)],
        );
        $failed = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed);

        self::assertSame(
            [
                'parserResult' => [
                    'success' => [
                        'sitemaps' => [$url],
                        'urls' => [$url],
                    ],
                ],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatParserResultAddsFailedResult(): void
    {
        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult([new Src\Sitemap\Sitemap(new Psr7\Uri($url))]);

        $this->subject->formatParserResult($successful, $failed);

        self::assertSame(
            [
                'parserResult' => [
                    'failure' => [
                        'sitemaps' => [$url],
                    ],
                ],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultDoesNotAddUrlsIfResultDoesNotContainUrls(): void
    {
        $result = new Src\Result\CacheWarmupResult();

        $this->subject->formatCacheWarmupResult($result);

        self::assertSame(
            [
                'cacheWarmupResult' => [],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultAddsSuccessfulUrls(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createSuccessful(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        self::assertSame(
            [
                'cacheWarmupResult' => [
                    'success' => [$url],
                ],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultAddsFailedUrls(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createFailed(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        self::assertSame(
            [
                'cacheWarmupResult' => [
                    'failure' => [$url],
                ],
            ],
            $this->subject->getJson(),
        );
    }
}
