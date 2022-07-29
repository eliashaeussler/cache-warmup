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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Command;

use EliasHaeussler\CacheWarmup\Command\CacheWarmupCommand;
use EliasHaeussler\CacheWarmup\Tests\Unit\Crawler\DummyCrawler;
use EliasHaeussler\CacheWarmup\Tests\Unit\Crawler\DummyVerboseCrawler;
use EliasHaeussler\CacheWarmup\Tests\Unit\RequestProphecyTrait;
use Exception;
use Generator;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CacheWarmupCommandTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmupCommandTest extends TestCase
{
    use ProphecyTrait;
    use RequestProphecyTrait;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected function setUp(): void
    {
        $command = new CacheWarmupCommand();
        $application = new Application();
        $application->add($command);
        $this->commandTester = new CommandTester($application->find('cache-warmup'));
        $this->clientProphecy = $this->prophesize(ClientInterface::class);
        $command->setClient($this->clientProphecy->reveal());
    }

    /**
     * @test
     */
    public function interactThrowsExceptionIfNeitherArgumentNorInteractiveInputProvidesSitemaps(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1604258903);

        $this->commandTester->setInputs([null]);
        $this->commandTester->execute([]);
    }

    /**
     * @test
     */
    public function executeThrowsExceptionIfNoSitemapsAreGivenAndInteractiveModeIsDisabled(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1604261236);

        $this->commandTester->execute([], ['interactive' => false]);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeUsesSitemapUrlsFromInteractiveUserInputIfSitemapsArgumentIsNotGiven(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->setInputs([
            'https://www.example.com/sitemap.xml',
            null,
        ]);
        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('* https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('* https://www.example.com/', $output);
        self::assertStringContainsString('* https://www.example.com/foo', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeCrawlsUrlsFromGivenSitemaps(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('* https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('* https://www.example.com/', $output);
        self::assertStringContainsString('* https://www.example.com/foo', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeLimitsCrawlingIfLimitOptionIsSet(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--limit' => 1,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('* https://www.example.com/sitemap.xml', $output);
        self::assertStringContainsString('* https://www.example.com/', $output);
        self::assertStringNotContainsString('* https://www.example.com/foo', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeCrawlsAdditionalUrls(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->setInputs([null]);
        $this->commandTester->execute(
            [
                '--urls' => [
                    'https://www.example.com/',
                    'https://www.example.com/foo',
                ],
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            ]
        );

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('* https://www.example.com/', $output);
        self::assertStringContainsString('* https://www.example.com/foo', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeHidesVerboseOutputIfVerbosityIsNormal(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
        ]);

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Parsing sitemaps... Done', $output);
        self::assertStringContainsString('Crawling URLs... Done', $output);
        self::assertStringNotContainsString('* https://www.example.com/sitemap.xml', $output);
        self::assertStringNotContainsString('* https://www.example.com/', $output);
        self::assertStringNotContainsString('* https://www.example.com/foo', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeThrowsExceptionIfGivenCrawlerClassDoesNotExist(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1604261816);

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => 'foo',
        ]);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeThrowsExceptionIfGivenCrawlerClassIsNotValid(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1604261885);

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => Exception::class,
        ]);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeUsesCustomCrawler(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => DummyCrawler::class,
        ]);

        $expected = [
            new Uri('https://www.example.com/'),
            new Uri('https://www.example.com/foo'),
        ];

        self::assertEquals($expected, DummyCrawler::$crawledUrls);
    }

    /**
     * @test
     */
    public function executeThrowsExceptionIfCrawlerOptionsAreInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1659120649);
        $this->expectExceptionMessage('The given crawler options are invalid. Please pass crawler options as JSON-encoded array.');

        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler-options' => 'foo',
        ]);
    }

    /**
     * @test
     * @dataProvider executeUsesCrawlerOptionsDataProvider
     *
     * @param array{concurrency: int}|string $crawlerOptions
     */
    public function executeUsesCrawlerOptions($crawlerOptions): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute(
            [
                'sitemaps' => [
                    'https://www.example.com/sitemap.xml',
                ],
                '--crawler-options' => $crawlerOptions,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            ]
        );

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('Using custom crawler options:', $output);
        self::assertStringContainsString('* concurrency: 3', $output);
    }

    /**
     * @test
     */
    public function executeShowsWarningIfCrawlerOptionsArePassedToNonConfigurableCrawler(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => DummyCrawler::class,
            '--crawler-options' => ['foo' => 'bar'],
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('You\'ve passed crawler options to a non-configurable crawler.', $output);
    }

    /**
     * @test
     *
     * @throws ClientExceptionInterface
     */
    public function executeAppliesOutputToVerboseCrawler(): void
    {
        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--crawler' => DummyVerboseCrawler::class,
        ]);

        self::assertSame($this->commandTester->getOutput(), DummyVerboseCrawler::$output);
    }

    /**
     * @test
     * @dataProvider executeFailsIfSitemapCannotBeCrawledDataProvider
     */
    public function executeFailsIfSitemapCannotBeCrawled(bool $allowFailures, int $expected): void
    {
        DummyCrawler::$simulateFailure = true;

        $this->prophesizeSitemapRequest('valid_sitemap_3');
        $exitCode = $this->commandTester->execute([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
            ],
            '--allow-failures' => $allowFailures,
        ]);

        self::assertSame($expected, $exitCode);
    }

    /**
     * @return Generator<string, array{array{concurrency: int}|string}>
     */
    public function executeUsesCrawlerOptionsDataProvider(): Generator
    {
        yield 'array' => [['concurrency' => 3]];
        yield 'json string' => ['{"concurrency": 3}'];
    }

    /**
     * @return \Generator<string, array{bool, int}>
     */
    public function executeFailsIfSitemapCannotBeCrawledDataProvider(): Generator
    {
        yield 'with --allow-failures' => [true, 0];
        yield 'without --allow-failures' => [false, 1];
    }

    protected function tearDown(): void
    {
        DummyCrawler::$crawledUrls = [];
        DummyCrawler::$simulateFailure = false;
        DummyVerboseCrawler::$output = null;
    }
}
