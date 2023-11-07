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

namespace EliasHaeussler\CacheWarmup\Tests\Formatter;

use EliasHaeussler\CacheWarmup as Src;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * JsonFormatterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Formatter\JsonFormatter::class)]
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
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

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
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

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
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

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
    public function formatParserResultAddsExcludedResult(): void
    {
        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult(
            [new Src\Sitemap\Sitemap(new Psr7\Uri($url))],
            [new Src\Sitemap\Url($url)],
        );

        $this->subject->formatParserResult($successful, $failed, $excluded);

        self::assertSame(
            [
                'parserResult' => [
                    'excluded' => [
                        'sitemaps' => [$url],
                        'urls' => [$url],
                    ],
                ],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatParserResultAddsDuration(): void
    {
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();
        $duration = new Src\Time\Duration(123.45);

        $this->subject->formatParserResult($successful, $failed, $excluded, $duration);

        self::assertSame(
            [
                'time' => [
                    'parse' => $duration->format(),
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

        self::assertSame([], $this->subject->getJson());
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

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultAddsCancelledState(): void
    {
        $result = new Src\Result\CacheWarmupResult();
        $result->setCancelled(true);

        $this->subject->formatCacheWarmupResult($result);

        self::assertSame(
            [
                'cacheWarmupResult' => [
                    'cancelled' => true,
                ],
            ],
            $this->subject->getJson(),
        );
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultAddsDuration(): void
    {
        $result = new Src\Result\CacheWarmupResult();
        $duration = new Src\Time\Duration(123.45);

        $this->subject->formatCacheWarmupResult($result, $duration);

        self::assertSame(
            [
                'time' => [
                    'crawl' => $duration->format(),
                ],
            ],
            $this->subject->getJson(),
        );
    }

    /**
     * @param array{messages: array<value-of<Src\Formatter\MessageSeverity>, list<string>>} $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('logMessageAddsMessageDataProvider')]
    public function logMessageAddsMessage(Src\Formatter\MessageSeverity $severity, array $expected): void
    {
        $this->subject->logMessage('foo', $severity);

        self::assertSame($expected, $this->subject->getJson());
    }

    /**
     * @return Generator<string, array{Src\Formatter\MessageSeverity, array{messages: array<value-of<Src\Formatter\MessageSeverity>, list<string>>}}>
     */
    public static function logMessageAddsMessageDataProvider(): Generator
    {
        $message = static fn (Src\Formatter\MessageSeverity $severity) => [
            'messages' => [
                $severity->value => [
                    'foo',
                ],
            ],
        ];

        yield 'error' => [Src\Formatter\MessageSeverity::Error, $message(Src\Formatter\MessageSeverity::Error)];
        yield 'info' => [Src\Formatter\MessageSeverity::Info, $message(Src\Formatter\MessageSeverity::Info)];
        yield 'success' => [Src\Formatter\MessageSeverity::Success, $message(Src\Formatter\MessageSeverity::Success)];
        yield 'warning' => [Src\Formatter\MessageSeverity::Warning, $message(Src\Formatter\MessageSeverity::Warning)];
    }
}
