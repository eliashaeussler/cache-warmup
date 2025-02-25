<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\DependencyInjection;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\EventDispatcher;

/**
 * ContainerFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\ContainerFactory::class)]
final class ContainerFactoryTest extends Framework\TestCase
{
    private Console\Output\NullOutput $output;
    private Log\NullLogger $logger;
    private EventDispatcher\EventDispatcher $eventDispatcher;
    private Src\Http\Client\ClientFactory $clientFactory;
    private Src\DependencyInjection\ContainerFactory $subject;

    public function setUp(): void
    {
        $this->output = new Console\Output\NullOutput();
        $this->logger = new Log\NullLogger();
        $this->eventDispatcher = new EventDispatcher\EventDispatcher();
        $this->clientFactory = new Src\Http\Client\ClientFactory($this->eventDispatcher);
        $this->subject = new Src\DependencyInjection\ContainerFactory(
            $this->output,
            $this->logger,
            $this->eventDispatcher,
            $this->clientFactory,
        );
    }

    #[Framework\Attributes\Test]
    public function buildReturnsCachedContainer(): void
    {
        $actual = $this->subject->build();

        self::assertSame($actual, $this->subject->build());
    }

    #[Framework\Attributes\Test]
    public function buildCreatesContainerForConstructableCrawlers(): void
    {
        $actual = $this->subject->build();

        $crawler = $actual->get(Tests\Fixtures\Classes\DummyCrawler::class);

        self::assertSame($this->eventDispatcher, $crawler->eventDispatcher);
        self::assertEquals($this->clientFactory->get(), Tests\Fixtures\Classes\DummyCrawler::$client);
    }

    #[Framework\Attributes\Test]
    public function buildCreatesContainerForConstructableParsers(): void
    {
        $actual = $this->subject->build();
        $actual->get(Tests\Fixtures\Classes\DummyParser::class);

        self::assertEquals($this->clientFactory->get(), Tests\Fixtures\Classes\DummyParser::$client);
    }

    #[Framework\Attributes\Test]
    public function buildRegistersComponentsAsServices(): void
    {
        $actual = $this->subject->build();

        self::assertSame($this->output, $actual->get(Console\Output\OutputInterface::class));
        self::assertSame($this->logger, $actual->get(Log\LoggerInterface::class));
        self::assertSame($this->eventDispatcher, $actual->get(EventDispatcherInterface::class));
        self::assertSame($this->clientFactory, $actual->get(Src\Http\Client\ClientFactory::class));
        self::assertEquals($this->clientFactory->get(), $actual->get(ClientInterface::class));
    }

    protected function tearDown(): void
    {
        Tests\Fixtures\Classes\DummyCrawler::$client = null;
    }
}
