<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\DeepClosureComparator;
use EliasHaeussler\TransientLogger;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\Console;

/**
 * CrawlerFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\CrawlerFactory::class)]
final class CrawlerFactoryTest extends Framework\TestCase
{
    private Console\Output\BufferedOutput $output;
    private TransientLogger\TransientLogger $logger;
    private Tests\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Src\Http\Client\ClientFactory $clientFactory;
    private Src\Crawler\CrawlerFactory $subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->logger = new TransientLogger\TransientLogger();
        $this->eventDispatcher = new Tests\Fixtures\Classes\DummyEventDispatcher();
        $this->clientFactory = new Src\Http\Client\ClientFactory(
            $this->eventDispatcher,
            [
                RequestOptions::AUTH => ['username', 'password'],
            ],
        );
        $this->subject = new Src\Crawler\CrawlerFactory(
            new Src\DependencyInjection\ContainerFactory(
                $this->output,
                $this->logger,
                $this->eventDispatcher,
                $this->clientFactory,
            ),
            Log\LogLevel::ERROR,
            true,
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenCrawlerClassIsInvalid(): void
    {
        $this->expectExceptionObject(new Src\Exception\CrawlerDoesNotExist('foo'));

        /* @phpstan-ignore argument.templateType */
        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenCrawlerClassIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\CrawlerIsInvalid(self::class));

        /* @phpstan-ignore argument.templateType */
        $this->subject->get(self::class);
    }

    #[Framework\Attributes\Test]
    public function getReturnsCrawler(): void
    {
        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyCrawler::class);

        self::assertSame($this->eventDispatcher, $actual->eventDispatcher);
        DeepClosureComparator\DeepClosureAssert::assertEquals($this->clientFactory->get(), Tests\Fixtures\Classes\DummyCrawler::$client);
    }

    #[Framework\Attributes\Test]
    public function getReturnsConfigurableCrawler(): void
    {
        $options = [
            'foo' => 'baz',
        ];

        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyConfigurableCrawler::class, $options);

        self::assertSame(['foo' => 'baz', 'bar' => 42], $actual->getOptions());
    }

    #[Framework\Attributes\Test]
    public function getReturnsVerboseCrawler(): void
    {
        $this->subject->get(Tests\Fixtures\Classes\DummyVerboseCrawler::class);

        self::assertSame($this->output, Tests\Fixtures\Classes\DummyVerboseCrawler::$output);

        Tests\Fixtures\Classes\DummyVerboseCrawler::$output = null;
    }

    #[Framework\Attributes\Test]
    public function getReturnsLoggingCrawler(): void
    {
        $this->subject->get(Tests\Fixtures\Classes\DummyLoggingCrawler::class);

        self::assertSame($this->logger, Tests\Fixtures\Classes\DummyLoggingCrawler::$logger);
        self::assertSame(Log\LogLevel::ERROR, Tests\Fixtures\Classes\DummyLoggingCrawler::$logLevel);

        Tests\Fixtures\Classes\DummyLoggingCrawler::$logger = null;
        Tests\Fixtures\Classes\DummyLoggingCrawler::$logLevel = null;
    }

    #[Framework\Attributes\Test]
    public function getReturnsStoppableCrawler(): void
    {
        $this->subject->get(Tests\Fixtures\Classes\DummyStoppableCrawler::class);

        self::assertTrue(Tests\Fixtures\Classes\DummyStoppableCrawler::$stopOnFailure);

        Tests\Fixtures\Classes\DummyStoppableCrawler::$stopOnFailure = false;
    }

    #[Framework\Attributes\Test]
    public function getDispatchesCrawlerConstructedEvent(): void
    {
        $this->subject->get(Tests\Fixtures\Classes\DummyCrawler::class);

        self::assertTrue(
            $this->eventDispatcher->wasDispatched(Src\Event\Crawler\CrawlerConstructed::class),
        );
    }

    #[Framework\Attributes\Test]
    public function validateThrowsExceptionIfGivenCrawlerClassIsInvalid(): void
    {
        $this->expectExceptionObject(new Src\Exception\CrawlerDoesNotExist('foo'));

        Src\Crawler\CrawlerFactory::validate('foo');
    }

    #[Framework\Attributes\Test]
    public function validateThrowsExceptionIfGivenCrawlerClassIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\CrawlerIsInvalid(self::class));

        Src\Crawler\CrawlerFactory::validate(self::class);
    }

    protected function tearDown(): void
    {
        Tests\Fixtures\Classes\DummyCrawler::$client = null;
    }
}
