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
 * TextFormatterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Formatter\TextFormatter::class)]
final class TextFormatterTest extends Framework\TestCase
{
    private Console\Output\BufferedOutput $output;
    private Src\Formatter\TextFormatter $subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Src\Formatter\TextFormatter(
            new Console\Style\SymfonyStyle(new Console\Input\StringInput(''), $this->output),
        );
    }

    #[Framework\Attributes\Test]
    public function formatParserResultDoesNotPrintSuccessfulResultIfOutputIsNotVeryVerbose(): void
    {
        $successful = new Src\Result\ParserResult([new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com'))]);
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

        self::assertSame('', $this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function formatParserResultPrintsSuccessfulSitemapsIfOutputIsVerbose(): void
    {
        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_VERBOSE);

        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult(
            [new Src\Sitemap\Sitemap(new Psr7\Uri($url))],
            [new Src\Sitemap\Url($url.'/foo')],
        );
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Parsed sitemaps', $output);
        self::assertStringContainsString('DONE  https://www.example.com', $output);
        self::assertStringNotContainsString('DONE  https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function formatParserResultPrintsSuccessfulUrlsIfOutputIsDebug(): void
    {
        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_DEBUG);

        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult(
            [new Src\Sitemap\Sitemap(new Psr7\Uri($url))],
            [new Src\Sitemap\Url($url.'/foo')],
        );
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Parsed URLs', $output);
        self::assertStringContainsString('DONE  https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatParserResultPrintsFailedResult(): void
    {
        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult([new Src\Sitemap\Sitemap(new Psr7\Uri($url))]);
        $excluded = new Src\Result\ParserResult();

        $this->subject->formatParserResult($successful, $failed, $excluded);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('FAIL  https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatParserResultPrintsExcludedResult(): void
    {
        $url = 'https://www.example.com';
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult(
            [new Src\Sitemap\Sitemap(new Psr7\Uri($url))],
            [new Src\Sitemap\Url($url)],
        );

        $this->subject->formatParserResult($successful, $failed, $excluded);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('SKIP  https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatParserResultDoesNotPrintDurationIfOutputIsNotDebug(): void
    {
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();
        $duration = new Src\Time\Duration(500);

        $this->subject->formatParserResult($successful, $failed, $excluded, $duration);

        self::assertEmpty($this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function formatParserResultPrintsDuration(): void
    {
        $successful = new Src\Result\ParserResult();
        $failed = new Src\Result\ParserResult();
        $excluded = new Src\Result\ParserResult();
        $duration = new Src\Time\Duration(500);

        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_DEBUG);

        $this->subject->formatParserResult($successful, $failed, $excluded, $duration);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Parsing finished in 0.5s', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultDoesNotPrintUrlsIfResultDoesNotContainUrls(): void
    {
        $result = new Src\Result\CacheWarmupResult();

        $this->subject->formatCacheWarmupResult($result);

        self::assertEmpty($this->output->fetch());
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultDoesNotPrintSuccessfulUrlsIfOutputIsNotVeryVerbose(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createSuccessful(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringNotContainsString('The following URLs were successfully crawled:', $output);
        self::assertStringNotContainsString('* https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsSuccessfulUrlsIfOutputIsDebug(): void
    {
        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_DEBUG);

        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createSuccessful(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('DONE  https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultDoesNotPrintFailedUrlsIfOutputIsNotVerbose(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createFailed(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringNotContainsString('https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsFailedUrlsIfOutputIsVerbose(): void
    {
        $this->output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_VERBOSE);

        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createFailed(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('FAIL  https://www.example.com', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsResultMessageOnSuccess(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createSuccessful(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Successfully warmed up caches for 1 URL.', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsResultMessageOnFailure(): void
    {
        $url = 'https://www.example.com';
        $result = new Src\Result\CacheWarmupResult();
        $result->addResult(Src\Result\CrawlingResult::createFailed(new Psr7\Uri($url)));

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Failed to warm up caches for 1 URL.', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsResultMessageOnCancelledCacheWarmup(): void
    {
        $result = new Src\Result\CacheWarmupResult();
        $result->setCancelled(true);

        $this->subject->formatCacheWarmupResult($result);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Cache warmup was cancelled due to a crawling failure.', $output);
    }

    #[Framework\Attributes\Test]
    public function formatCacheWarmupResultPrintsDuration(): void
    {
        $result = new Src\Result\CacheWarmupResult();
        $duration = new Src\Time\Duration(500);

        $this->subject->formatCacheWarmupResult($result, $duration);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Crawling finished in 0.5s', $output);
    }

    /**
     * @param non-empty-string $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('logMessagePrintsGivenMessageWithGivenSeverityDataProvider')]
    public function logMessagePrintsGivenMessageWithGivenSeverity(
        Src\Formatter\MessageSeverity $severity,
        string $expected,
    ): void {
        $this->subject->logMessage('foo', $severity);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString($expected, $output);
    }

    /**
     * @return Generator<string, array{Src\Formatter\MessageSeverity, non-empty-string}>
     */
    public static function logMessagePrintsGivenMessageWithGivenSeverityDataProvider(): Generator
    {
        yield 'error' => [Src\Formatter\MessageSeverity::Error, '[ERROR] foo'];
        yield 'info' => [Src\Formatter\MessageSeverity::Info, '[INFO] foo'];
        yield 'success' => [Src\Formatter\MessageSeverity::Success, '[OK] foo'];
        yield 'warning' => [Src\Formatter\MessageSeverity::Warning, '[WARNING] foo'];
    }
}
