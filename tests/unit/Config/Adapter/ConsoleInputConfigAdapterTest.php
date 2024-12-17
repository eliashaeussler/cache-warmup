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

namespace EliasHaeussler\CacheWarmup\Tests\Config\Adapter;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\Console;

/**
 * ConsoleInputConfigAdapterTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Config\Adapter\ConsoleInputConfigAdapter::class)]
final class ConsoleInputConfigAdapterTest extends Framework\TestCase
{
    private Console\Input\InputDefinition $inputDefinition;

    public function setUp(): void
    {
        $this->inputDefinition = (new Src\Command\CacheWarmupCommand())->getDefinition();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredCrawlerOptionsAreInvalid(): void
    {
        $subject = $this->getSubject([
            '--crawler-options' => 'foo',
        ]);

        $this->expectExceptionObject(new Src\Exception\OptionsAreMalformed('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredCrawlerIsInvalid(): void
    {
        $subject = $this->getSubject([
            '--crawler' => 'foo',
        ]);

        $this->expectExceptionObject(new Src\Exception\CrawlerDoesNotExist('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getParsesCrawlerOptions(): void
    {
        $subject = $this->getSubject([
            '--crawler-options' => '{"foo":"baz"}',
        ]);

        self::assertSame(['foo' => 'baz'], $subject->get()->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredParserOptionsAreInvalid(): void
    {
        $subject = $this->getSubject([
            '--parser-options' => 'foo',
        ]);

        $this->expectExceptionObject(new Src\Exception\OptionsAreMalformed('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConfiguredParserIsInvalid(): void
    {
        $subject = $this->getSubject([
            '--parser' => 'foo',
        ]);

        $this->expectExceptionObject(new Src\Exception\ParserDoesNotExist('foo'));

        $subject->get();
    }

    #[Framework\Attributes\Test]
    public function getParsesParserOptions(): void
    {
        $subject = $this->getSubject([
            '--parser-options' => '{"foo":"baz"}',
        ]);

        self::assertSame(['foo' => 'baz'], $subject->get()->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function getMapsConsoleInputToCacheWarmupConfig(): void
    {
        $subject = $this->getSubject([
            'sitemaps' => [
                'https://www.example.com/sitemap.xml',
                'https://www.example.com/sitemap_en.xml',
            ],
            '--urls' => [
                'https://www.example.com/',
                'https://www.example.com/foo',
            ],
            '--exclude' => [
                '#foo#',
                '*foo*',
            ],
            '--limit' => 10,
            '--progress' => true,
            '--crawler' => Tests\Fixtures\Classes\DummyCrawler::class,
            '--crawler-options' => '{"foo":"baz"}',
            '--strategy' => Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            '--parser' => Tests\Fixtures\Classes\DummyParser::class,
            '--parser-options' => '{"foo":"baz"}',
            '--format' => Src\Formatter\JsonFormatter::getType(),
            '--log-file' => 'errors.log',
            '--log-level' => Log\LogLevel::DEBUG,
            '--allow-failures' => true,
            '--stop-on-failure' => true,
            '--repeat-after' => 300,
        ]);

        $expected = new Src\Config\CacheWarmupConfig(
            [
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap.xml'),
                Src\Sitemap\Sitemap::createFromString('https://www.example.com/sitemap_en.xml'),
            ],
            [
                new Src\Sitemap\Url('https://www.example.com/'),
                new Src\Sitemap\Url('https://www.example.com/foo'),
            ],
            [
                Src\Config\Option\ExcludePattern::createFromRegularExpression('#foo#'),
                Src\Config\Option\ExcludePattern::createFromPattern('*foo*'),
            ],
            10,
            true,
            Tests\Fixtures\Classes\DummyCrawler::class,
            ['foo' => 'baz'],
            Src\Crawler\Strategy\SortByChangeFrequencyStrategy::getName(),
            Tests\Fixtures\Classes\DummyParser::class,
            ['foo' => 'baz'],
            Src\Formatter\JsonFormatter::getType(),
            'errors.log',
            Log\LogLevel::DEBUG,
            true,
            true,
            300,
        );

        self::assertEquals($expected, $subject->get());
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionOnInvalidCommandInput(): void
    {
        $subject = $this->getSubject([
            // Must be a non-negative integer
            '--limit' => 'foo',
        ]);

        $this->expectException(Src\Exception\CommandParametersAreInvalid::class);
        $this->expectExceptionCode(1708712872);

        $subject->get();
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getSubject(array $parameters = []): Src\Config\Adapter\ConsoleInputConfigAdapter
    {
        return new Src\Config\Adapter\ConsoleInputConfigAdapter(
            new Console\Input\ArrayInput($parameters, $this->inputDefinition),
        );
    }
}
