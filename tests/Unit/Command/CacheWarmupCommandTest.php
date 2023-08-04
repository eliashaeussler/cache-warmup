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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Command;

use EliasHaeussler\CacheWarmup\Command;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Formatter;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\CacheWarmup\Tests;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

use function implode;

/**
 * CacheWarmupCommandTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmupCommandTest extends Framework\TestCase
{
    use Tests\Unit\ClientMockTrait;

    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->client = $this->createClient();

        $command = new Command\CacheWarmupCommand($this->client);
        $application = new Console\Application();
        $application->add($command);

        $this->commandTester = new Console\Tester\CommandTester($command);
    }

    #[Framework\Attributes\Test]
    public function initializeThrowsExceptionIfGivenFormatterIsUnsupported(): void
    {
        $this->expectExceptionObject(Exception\UnsupportedFormatterException::create('foo'));

        $this->commandTester->execute(['--format' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function initializeHidesOutputForCrawlersIfGivenFormatterIsNotVerbose(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--format' => 'json',
                '--progress' => true,
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE,
            ],
        );

        self::assertSame('', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function interactThrowsExceptionIfNeitherArgumentNorInteractiveInputProvidesSitemaps(): void
    {
        $this->expectException(Console\Exception\RuntimeException::class);
        $this->expectExceptionCode(1604258903);

        $this->commandTester->setInputs([null]);
        $this->commandTester->execute([]);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfNoSitemapsAreGivenAndInteractiveModeIsDisabled(): void
    {
        $this->expectException(Console\Exception\RuntimeException::class);
        $this->expectExceptionCode(1604261236);

        $this->commandTester->execute([], ['interactive' => false]);
    }

    #[Framework\Attributes\Test]
    public function executeUsesSitemapUrlsFromInteractiveUserInputIfSitemapsArgumentIsNotGiven(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->setInputs([
            'https://www.example.com/sitemap.xml',
            null,
        ]);

        $this->commandTester->execute([], ['verbosity' => Console\Output\OutputInterface::VERBOSITY_DEBUG]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('DONE  https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('DONE  https://www.example.com/', $output);
        self::assertStringContainsString('DONE  https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsUrlsFromGivenSitemaps(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_DEBUG,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('DONE  https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('DONE  https://www.example.com/', $output);
        self::assertStringContainsString('DONE  https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function executeLimitsCrawlingIfLimitOptionIsSet(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--limit' => 1,
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_DEBUG,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('DONE  https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('DONE  https://www.example.com/', $output);
        self::assertStringNotContainsString('https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsAdditionalUrls(): void
    {
        $this->commandTester->setInputs([null]);

        $this->commandTester->execute(
            [
                '--urls' => [
                    'https://www.example.com/',
                    'https://www.example.com/foo',
                ],
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('https://www.example.com/', $output);
        self::assertStringContainsString('https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function executeExcludesUrlsByGivenPatterns(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--exclude' => [
                '*/foo',
            ],
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('SKIP  https://www.example.com/foo', $output);
    }

    #[Framework\Attributes\Test]
    public function executeDoesNotShowProgressBarIfProgressOptionIsNotSet(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_VERBOSE,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringNotContainsString('100%', $output);
    }

    #[Framework\Attributes\Test]
    public function executeShowsCompactProgressBarIfProgressOptionIsSetAndOutputIsNormal(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--progress' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringNotContainsString(' DONE ', $output);
        self::assertStringContainsString('100%', $output);
    }

    #[Framework\Attributes\Test]
    public function executeShowsVerboseProgressBarIfProgressOptionIsSetAndOutputIsVerbose(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--progress' => true,
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_VERBOSE,
                'capture_stderr_separately' => true,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString(' DONE ', $output);
        self::assertStringContainsString('100%', $output);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfGivenCrawlerClassDoesNotExist(): void
    {
        $this->expectExceptionObject(Exception\InvalidCrawlerException::forMissingClass('foo'));

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => 'foo',
        ]);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfGivenCrawlerClassIsNotValid(): void
    {
        $this->expectExceptionObject(Exception\InvalidCrawlerException::forUnsupportedClass(self::class));

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => self::class,
        ]);
    }

    #[Framework\Attributes\Test]
    public function executeUsesCustomCrawler(): void
    {
        $origin = new Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Unit\Crawler\DummyCrawler::class,
        ]);

        $expected = [
            new Sitemap\Url('https://www.example.com/', origin: $origin),
            new Sitemap\Url('https://www.example.com/foo', origin: $origin),
        ];

        self::assertEquals($expected, Tests\Unit\Crawler\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfCrawlerOptionsAreInvalid(): void
    {
        $this->expectExceptionObject(Exception\InvalidCrawlerOptionException::forInvalidType('foo'));

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler-options' => 'foo',
        ]);
    }

    /**
     * @param array{concurrency: int}|string $crawlerOptions
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('executeUsesCrawlerOptionsDataProvider')]
    public function executeUsesCrawlerOptions(array|string $crawlerOptions): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--crawler-options' => $crawlerOptions,
            ],
            [
                'verbosity' => Console\Output\OutputInterface::VERBOSITY_VERBOSE,
            ],
        );

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Using custom crawler options:', $output);
        self::assertStringContainsString('"concurrency": 3', $output);
    }

    #[Framework\Attributes\Test]
    public function executeShowsWarningIfCrawlerOptionsArePassedToNonConfigurableCrawler(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Unit\Crawler\DummyCrawler::class,
            '--crawler-options' => ['foo' => 'bar'],
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('You passed crawler options for a non-configurable crawler.', $output);
    }

    #[Framework\Attributes\Test]
    public function executeAppliesOutputToVerboseCrawler(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Unit\Crawler\DummyVerboseCrawler::class,
        ]);

        self::assertSame($this->commandTester->getOutput(), Tests\Unit\Crawler\DummyVerboseCrawler::$output);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('executeFailsIfSitemapCannotBeCrawledDataProvider')]
    public function executeFailsIfSitemapCannotBeCrawled(bool $allowFailures, int $expected): void
    {
        Tests\Unit\Crawler\DummyCrawler::$resultStack[] = Result\CrawlingState::Failed;

        $this->mockSitemapRequest('valid_sitemap_3');

        $exitCode = $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--allow-failures' => $allowFailures,
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertSame($expected, $exitCode);
        self::assertNotEmpty($output);
        self::assertStringContainsString('Failed to warm up caches for 1 URL.', $output);
    }

    #[Framework\Attributes\Test]
    public function executePrintsSitemapsThatCouldNotBeParsed(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $exitCode = $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--allow-failures' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertSame(0, $exitCode);
        self::assertNotEmpty($output);
        self::assertStringContainsString('FAIL  https://www.example.com/sitemap.xml', $output);
    }

    #[Framework\Attributes\Test]
    public function executeFailsIfSitemapCannotBeParsed(): void
    {
        $this->mockSitemapRequest('invalid_sitemap_1');

        $this->expectException(Exception\InvalidSitemapException::class);
        $this->expectExceptionCode(1660668799);
        $this->expectExceptionMessage(
            implode(PHP_EOL, [
                'The sitemap "https://www.example.com/sitemap.xml" is invalid and cannot be parsed due to the following errors:',
                '  * The given URL must not be empty.',
            ]),
        );

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function executeUsesConfiguredFormatter(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--format' => Formatter\JsonFormatter::getType(),
        ]);

        // At this point, we cannot test the actual output of the JSON formatter
        // because it's applied on destructuring first
        self::assertSame('', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function executeRepeatsCacheWarmupIfEndlessModeIsEnabled(): void
    {
        Tests\Unit\Crawler\DummyCrawler::$resultStack[] = Result\CrawlingState::Successful;
        Tests\Unit\Crawler\DummyCrawler::$resultStack[] = Result\CrawlingState::Failed;

        $this->mockSitemapRequest('valid_sitemap_3');
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Unit\Crawler\DummyCrawler::class,
            '--repeat-after' => '1',
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString(
            '[WARNING] Command is scheduled to run forever. It will be repeated 1 second after each run.',
            $output,
        );
        self::assertStringContainsString('[OK] Successfully warmed up caches for 2 URLs.', $output);
        self::assertStringContainsString('[ERROR] Failed to warm up caches for 2 URLs.', $output);
    }

    /**
     * @return Generator<string, array{array{concurrency: int}|string}>
     */
    public static function executeUsesCrawlerOptionsDataProvider(): Generator
    {
        yield 'array' => [['concurrency' => 3]];
        yield 'json string' => ['{"concurrency": 3}'];
    }

    /**
     * @return Generator<string, array{bool, int}>
     */
    public static function executeFailsIfSitemapCannotBeCrawledDataProvider(): Generator
    {
        yield 'with --allow-failures' => [true, 0];
        yield 'without --allow-failures' => [false, 1];
    }

    protected function tearDown(): void
    {
        Tests\Unit\Crawler\DummyCrawler::$crawledUrls = [];
        Tests\Unit\Crawler\DummyCrawler::$resultStack = [];
        Tests\Unit\Crawler\DummyVerboseCrawler::$output = null;
    }
}
