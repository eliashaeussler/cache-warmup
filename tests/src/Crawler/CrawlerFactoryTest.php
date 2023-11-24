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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\TransientLogger;
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
    private Src\Crawler\CrawlerFactory $subject;

    protected function setUp(): void
    {
        $this->output = new Console\Output\BufferedOutput();
        $this->logger = new TransientLogger\TransientLogger();
        $this->subject = new Src\Crawler\CrawlerFactory(
            $this->output,
            $this->logger,
            Log\LogLevel::ERROR,
            true,
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenCrawlerClassIsInvalid(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerException::forMissingClass('foo'));

        /* @phpstan-ignore-next-line */
        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenCrawlerClassIsUnsupported(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerException::forUnsupportedClass(self::class));

        /* @phpstan-ignore-next-line */
        $this->subject->get(self::class);
    }

    #[Framework\Attributes\Test]
    public function getReturnsCrawler(): void
    {
        self::assertInstanceOf(
            Tests\Fixtures\Classes\DummyCrawler::class,
            $this->subject->get(Tests\Fixtures\Classes\DummyCrawler::class),
        );
    }

    #[Framework\Attributes\Test]
    public function getReturnsConfigurableCrawler(): void
    {
        $options = [
            'foo' => 'baz',
        ];

        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyConfigurableCrawler::class, $options);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyConfigurableCrawler::class, $actual);
        self::assertSame(['foo' => 'baz', 'bar' => 42], $actual->getOptions());
    }

    #[Framework\Attributes\Test]
    public function getReturnsVerboseCrawler(): void
    {
        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyVerboseCrawler::class);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyVerboseCrawler::class, $actual);
        self::assertSame($this->output, Tests\Fixtures\Classes\DummyVerboseCrawler::$output);

        Tests\Fixtures\Classes\DummyVerboseCrawler::$output = null;
    }

    #[Framework\Attributes\Test]
    public function getReturnsLoggingCrawler(): void
    {
        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyLoggingCrawler::class);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyLoggingCrawler::class, $actual);
        self::assertSame($this->logger, Tests\Fixtures\Classes\DummyLoggingCrawler::$logger);
        self::assertSame(Log\LogLevel::ERROR, Tests\Fixtures\Classes\DummyLoggingCrawler::$logLevel);

        Tests\Fixtures\Classes\DummyLoggingCrawler::$logger = null;
        Tests\Fixtures\Classes\DummyLoggingCrawler::$logLevel = null;
    }

    #[Framework\Attributes\Test]
    public function getReturnsStoppableCrawler(): void
    {
        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyStoppableCrawler::class);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyStoppableCrawler::class, $actual);
        self::assertTrue(Tests\Fixtures\Classes\DummyStoppableCrawler::$stopOnFailure);

        Tests\Fixtures\Classes\DummyStoppableCrawler::$stopOnFailure = false;
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsReturnsEmptyArrayOnNull(): void
    {
        self::assertSame([], $this->subject->parseCrawlerOptions(null));
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsThrowsExceptionOnMalformedJson(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerOptionException::forInvalidType(''));

        $this->subject->parseCrawlerOptions('');
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsThrowsExceptionIfJsonEncodedOptionsAreInvalid(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerOptionException::forInvalidType('"foo"'));

        $this->subject->parseCrawlerOptions('"foo"');
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsThrowsExceptionOnNonAssociativeArray(): void
    {
        $this->expectExceptionObject(Src\Exception\InvalidCrawlerOptionException::forInvalidType(['foo']));

        /* @phpstan-ignore-next-line */
        $this->subject->parseCrawlerOptions(['foo']);
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsCanUseJsonEncodedString(): void
    {
        $options = '{"foo":"baz"}';
        $expected = ['foo' => 'baz'];

        self::assertSame($expected, $this->subject->parseCrawlerOptions($options));
    }

    #[Framework\Attributes\Test]
    public function parseCrawlerOptionsReturnsCrawlerOptions(): void
    {
        $options = ['foo' => 'baz'];
        $expected = ['foo' => 'baz'];

        self::assertSame($expected, $this->subject->parseCrawlerOptions($options));
    }
}
