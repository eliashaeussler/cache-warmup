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

namespace EliasHaeussler\CacheWarmup\Tests\Command;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Symfony\Component\Console;

use function dirname;
use function file_get_contents;
use function putenv;
use function sys_get_temp_dir;
use function uniqid;

/**
 * CacheWarmupCommandTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Command\CacheWarmupCommand::class)]
final class CacheWarmupCommandTest extends Framework\TestCase
{
    use Tests\ClientMockTrait;

    private Tests\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->eventDispatcher = new Tests\Fixtures\Classes\DummyEventDispatcher();

        $command = new Src\Command\CacheWarmupCommand($this->client, $this->eventDispatcher);
        $application = new Console\Application();
        $application->add($command);

        $this->commandTester = new Console\Tester\CommandTester($command);
    }

    #[Framework\Attributes\Test]
    public function initializeThrowsExceptionIfUnsupportedConfigFileIsGiven(): void
    {
        $configFile = dirname(__DIR__).'/Fixtures/ConfigFiles/invalid_config.txt';

        $this->expectExceptionObject(new Src\Exception\ConfigFileIsNotSupported($configFile));

        $this->commandTester->execute(['--config' => $configFile]);
    }

    #[Framework\Attributes\Test]
    public function initializeResolvesRelativeConfigFilePath(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');

        $this->commandTester->execute(['--config' => 'tests/unit/Fixtures/ConfigFiles/valid_config.php']);

        self::assertStringContainsString('3/3', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('initializeLoadsConfigFromGivenFileDataProvider')]
    public function initializeLoadsConfigFromGivenFile(string $configFile): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');
        $this->mockSitemapRequest('valid_sitemap_2');

        $this->commandTester->execute(['--config' => $configFile, '--progress' => true]);

        self::assertStringContainsString('3/3', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function initializeOverwritesConfigFromGivenFileWithCommandOptions(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');

        $this->commandTester->execute([
            '--config' => dirname(__DIR__).'/Fixtures/ConfigFiles/valid_config.php',
            '--limit' => 1,
        ]);

        self::assertStringContainsString('1/1', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function initializeOverwritesConfigFromGivenFileDefinedAsEnvironmentVariable(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');

        putenv('CACHE_WARMUP_CONFIG='.dirname(__DIR__).'/Fixtures/ConfigFiles/valid_config.php');

        $this->commandTester->execute([
            '--limit' => 1,
        ]);

        putenv('CACHE_WARMUP_CONFIG');

        self::assertStringContainsString('1/1', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function initializeRespectsConfigFileDefinedAsEnvironmentVariableAndCommandOption(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');
        $this->mockSitemapRequest('valid_sitemap_4');
        $this->mockSitemapRequest('valid_sitemap_5');
        $this->mockSitemapRequest('valid_sitemap_3');

        putenv('CACHE_WARMUP_CONFIG='.dirname(__DIR__).'/Fixtures/ConfigFiles/valid_config.php');

        $this->commandTester->execute([
            '--config' => dirname(__DIR__).'/Fixtures/ConfigFiles/valid_config_sitemaps_only.php',
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
        ]);

        putenv('CACHE_WARMUP_CONFIG');

        $expected = [
            'https://www.example.org/',
            'https://www.example.org/foo',
            'https://www.example.org/baz',
            'https://www.example.com/',
            'https://www.example.com/foo',
        ];

        $actual = Tests\Fixtures\Classes\DummyCrawler::$crawledUrls;

        foreach ($expected as $i => $expectedUrl) {
            $actualUrl = $actual[$i] ?? null;

            self::assertInstanceOf(Src\Sitemap\Url::class, $actualUrl);
            self::assertSame($expectedUrl, (string) $actualUrl);
        }
    }

    #[Framework\Attributes\Test]
    public function initializeOverwritesConfigFromGivenFileAndCommandOptionsWithEnvironmentVariables(): void
    {
        $this->mockSitemapRequest('valid_sitemap_2');

        putenv('CACHE_WARMUP_LIMIT=2');

        $this->commandTester->execute([
            '--config' => dirname(__DIR__).'/Fixtures/ConfigFiles/valid_config.php',
            '--limit' => 1,
        ]);

        putenv('CACHE_WARMUP_LIMIT');

        self::assertStringContainsString('2/2', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function initializeDispatchesConfigResolvedEvent(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
        ]);

        self::assertTrue($this->eventDispatcher->wasDispatched(Src\Event\ConfigResolved::class));
    }

    #[Framework\Attributes\Test]
    public function initializeThrowsExceptionIfGivenFormatterIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\FormatterIsNotSupported('foo'));

        $this->commandTester->execute(['--format' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function initializeThrowsExceptionIfGivenLogLevelIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\LogLevelIsNotSupported('foo'));

        $this->commandTester->execute(['--log-level' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function initializeHidesOutputForCrawlersIfGivenFormatterIsNotVerbose(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--format' => 'json',
        ]);

        self::assertSame('', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function initializeImplicitlyEnablesProgressForErrorOutputIfGivenFormatterIsNotVerbose(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--format' => 'json',
            ],
            [
                'capture_stderr_separately' => true,
            ],
        );

        self::assertStringContainsString('100%', $this->commandTester->getErrorOutput());
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
        $this->expectExceptionObject(new Src\Exception\CrawlerDoesNotExist('foo'));

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
        $this->expectExceptionObject(new Src\Exception\CrawlerIsInvalid(self::class));

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
        $origin = new Src\Sitemap\Sitemap(new Psr7\Uri('https://www.example.com/sitemap.xml'));

        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
        ]);

        $expected = [
            new Src\Sitemap\Url('https://www.example.com/', origin: $origin),
            new Src\Sitemap\Url('https://www.example.com/foo', origin: $origin),
        ];

        self::assertEquals($expected, Tests\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfCrawlerOptionsAreInvalid(): void
    {
        $this->expectExceptionObject(new Src\Exception\CrawlerOptionIsInvalid('foo'));

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
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
            '--crawler-options' => ['foo' => 'bar'],
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('You passed crawler options to a non-configurable crawler.', $output);
    }

    #[Framework\Attributes\Test]
    public function executeShowsWarningIfStopOnFailureOptionIsPassedToNonStoppableCrawler(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
            '--stop-on-failure' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('You configured "stop on failure" for a non-stoppable crawler.', $output);
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfConfiguredCrawlingStrategyIsInvalid(): void
    {
        $this->expectException(Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The given crawling strategy is invalid.');
        $this->expectExceptionCode(1677618007);

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--strategy' => 'foo',
        ]);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsUrlsWithConfiguredStrategy(): void
    {
        $this->mockSitemapRequest('valid_sitemap_5');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
            '--strategy' => Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
        ]);

        $expected = [
            'https://www.example.org/baz',
            'https://www.example.org/foo',
            'https://www.example.org/',
        ];

        self::assertSame($expected, array_map(strval(...), Tests\Fixtures\Classes\DummyCrawler::$crawledUrls));
    }

    #[Framework\Attributes\Test]
    public function executeAppliesOutputToVerboseCrawler(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Fixtures\Classes\DummyVerboseCrawler::class,
        ]);

        self::assertSame($this->commandTester->getOutput(), Tests\Fixtures\Classes\DummyVerboseCrawler::$output);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('executeFailsIfSitemapCannotBeCrawledDataProvider')]
    public function executeFailsIfSitemapCannotBeCrawled(bool $allowFailures, int $expected): void
    {
        Tests\Fixtures\Classes\DummyCrawler::$resultStack[] = Src\Result\CrawlingState::Failed;

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

        $this->expectExceptionObject(
            new Src\Exception\SitemapIsMalformed(
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
            ),
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
            '--format' => Src\Formatter\JsonFormatter::getType(),
        ]);

        // At this point, we cannot test the actual output of the JSON formatter
        // because it's applied on destructuring first
        self::assertSame('', $this->commandTester->getDisplay());
    }

    #[Framework\Attributes\Test]
    public function executeLogsCrawlingResults(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $logFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('cache-warmup_tests_').DIRECTORY_SEPARATOR.'test.log';

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--log-file' => $logFile,
        ]);

        self::assertFileExists($logFile);
        self::assertNotEmpty((string) file_get_contents($logFile));
    }

    #[Framework\Attributes\Test]
    public function executeStopsOnFailureWithStopOnFailureOptionAndStoppableCrawlerGiven(): void
    {
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--stop-on-failure' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertNotEmpty($output);
        self::assertStringContainsString('Cancelled', $output);
    }

    #[Framework\Attributes\Test]
    public function executeRepeatsCacheWarmupIfEndlessModeIsEnabled(): void
    {
        Tests\Fixtures\Classes\DummyCrawler::$resultStack[] = Src\Result\CrawlingState::Successful;
        Tests\Fixtures\Classes\DummyCrawler::$resultStack[] = Src\Result\CrawlingState::Failed;

        $this->mockSitemapRequest('valid_sitemap_3');
        $this->mockSitemapRequest('valid_sitemap_3');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
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
     * @return Generator<string, array{string}>
     */
    public static function initializeLoadsConfigFromGivenFileDataProvider(): Generator
    {
        $fixturesPath = dirname(__DIR__).'/Fixtures/ConfigFiles';

        yield 'json' => [$fixturesPath.'/valid_config_sitemaps_only.json'];
        yield 'php' => [$fixturesPath.'/valid_config_sitemaps_only.php'];
        yield 'yaml' => [$fixturesPath.'/valid_config_sitemaps_only.yaml'];
        yield 'yml' => [$fixturesPath.'/valid_config_sitemaps_only.yml'];
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
        Tests\Fixtures\Classes\DummyCrawler::$crawledUrls = [];
        Tests\Fixtures\Classes\DummyCrawler::$resultStack = [];
        Tests\Fixtures\Classes\DummyVerboseCrawler::$output = null;
    }
}
