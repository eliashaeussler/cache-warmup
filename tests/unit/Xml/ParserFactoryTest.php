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

namespace EliasHaeussler\CacheWarmup\Tests\Xml;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;

/**
 * ParserFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Xml\ParserFactory::class)]
final class ParserFactoryTest extends Framework\TestCase
{
    private Src\Http\Client\ClientFactory $clientFactory;
    private Src\Xml\ParserFactory $subject;

    protected function setUp(): void
    {
        $this->clientFactory = new Src\Http\Client\ClientFactory([
            RequestOptions::AUTH => ['username', 'password'],
        ]);
        $this->subject = new Src\Xml\ParserFactory(
            new Src\DependencyInjection\ContainerFactory(clientFactory: $this->clientFactory),
        );
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenParserClassIsInvalid(): void
    {
        $this->expectExceptionObject(new Src\Exception\ParserDoesNotExist('foo'));

        /* @phpstan-ignore argument.type */
        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenParserClassIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\ParserIsInvalid(self::class));

        /* @phpstan-ignore argument.type */
        $this->subject->get(self::class);
    }

    #[Framework\Attributes\Test]
    public function getReturnsParser(): void
    {
        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyParser::class);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyParser::class, $actual);
        self::assertEquals($this->clientFactory->get(), Tests\Fixtures\Classes\DummyParser::$client);
    }

    #[Framework\Attributes\Test]
    public function getReturnsConfigurableParser(): void
    {
        $options = [
            'foo' => 'baz',
        ];

        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyConfigurableParser::class, $options);

        self::assertInstanceOf(Tests\Fixtures\Classes\DummyConfigurableParser::class, $actual);
        self::assertSame(['foo' => 'baz'], $actual->getOptions());
    }

    #[Framework\Attributes\Test]
    public function validateThrowsExceptionIfGivenParserClassIsInvalid(): void
    {
        $this->expectExceptionObject(new Src\Exception\ParserDoesNotExist('foo'));

        Src\Xml\ParserFactory::validate('foo');
    }

    #[Framework\Attributes\Test]
    public function validateThrowsExceptionIfGivenParserClassIsUnsupported(): void
    {
        $this->expectExceptionObject(new Src\Exception\ParserIsInvalid(self::class));

        Src\Xml\ParserFactory::validate(self::class);
    }

    protected function tearDown(): void
    {
        Tests\Fixtures\Classes\DummyParser::$client = null;
    }
}
