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
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework;
use Psr\EventDispatcher;
use stdClass;

/**
 * ContainerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\Container::class)]
final class ContainerTest extends Framework\TestCase
{
    private Src\DependencyInjection\Container $subject;

    public function setUp(): void
    {
        $this->subject = new Src\DependencyInjection\Container();
    }

    #[Framework\Attributes\Test]
    public function getReturnsCachedService(): void
    {
        $expected = $this->subject->get(stdClass::class);

        self::assertSame($expected, $this->subject->get(stdClass::class));
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionOnRecursiveServiceCreation(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\RecursionInServiceCreation(Tests\Fixtures\Classes\DummyRecursiveClass::class),
        );

        $this->subject->get(Tests\Fixtures\Classes\DummyRecursiveClass::class);
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectExceptionObject(
            /* @phpstan-ignore argument.type */
            new Src\Exception\ClassDoesNotExist('foo'),
        );

        /* @phpstan-ignore argument.type */
        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    public function getCreatesNewInstanceWithoutConstructor(): void
    {
        $expected = new stdClass();

        self::assertEquals($expected, $this->subject->get(stdClass::class));
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenClassHasNonPublicConstructor(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ClassConstructorIsInaccessible(Src\Http\Message\RequestFactory::class),
        );

        $this->subject->get(Src\Http\Message\RequestFactory::class);
    }

    #[Framework\Attributes\Test]
    public function getAutowiresNonBuiltinConstructorParameter(): void
    {
        $eventDispatcher = new Tests\Fixtures\Classes\DummyEventDispatcher();

        $this->subject->set(ClientInterface::class, new Client());
        $this->subject->set(EventDispatcher\EventDispatcherInterface::class, $eventDispatcher);

        $actual = $this->subject->get(Tests\Fixtures\Classes\DummyCrawler::class);

        self::assertSame($eventDispatcher, $actual->eventDispatcher);

        Tests\Fixtures\Classes\DummyCrawler::$client = null;
    }

    #[Framework\Attributes\Test]
    public function getAutowiresOptionalConstructorParameter(): void
    {
        $class = new class {
            public function __construct(public string $foo = '') {}
        };

        $actual = $this->subject->get($class::class);

        self::assertSame('', $actual->foo);
    }

    #[Framework\Attributes\Test]
    public function getAutowiresNullableConstructorParameter(): void
    {
        $class = new class(null) {
            public function __construct(public ?string $foo) {}
        };

        $actual = $this->subject->get($class::class);

        self::assertNull($actual->foo);
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfConstructorParameterCannotBeAutowired(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ParameterCannotBeAutowired(Src\Formatter\JsonFormatter::class, 'io'),
        );

        $this->subject->get(Src\Formatter\JsonFormatter::class);
    }

    #[Framework\Attributes\Test]
    public function hasReturnsTrueIfServiceExistsOrCanBeCreated(): void
    {
        $this->subject->set(stdClass::class, new stdClass());

        self::assertTrue($this->subject->has(stdClass::class));
        self::assertTrue($this->subject->has(Exception::class));
    }

    #[Framework\Attributes\Test]
    public function hasReturnsFalseIfServiceCannotBeCreated(): void
    {
        self::assertFalse($this->subject->has(self::class));
    }

    #[Framework\Attributes\Test]
    public function setInjectsGivenClass(): void
    {
        $object = new stdClass();

        $this->subject->set(stdClass::class, $object);

        self::assertSame($object, $this->subject->get(stdClass::class));
    }

    #[Framework\Attributes\Test]
    public function setInjectsGivenClassUsingGivenIdentifierAndActualClassName(): void
    {
        $object = new stdClass();

        /* @phpstan-ignore argument.type */
        $this->subject->set('foo', $object);

        /* @phpstan-ignore argument.type */
        self::assertSame($object, $this->subject->get('foo'));
        self::assertSame($object, $this->subject->get(stdClass::class));
    }
}
