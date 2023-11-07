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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use Exception;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use Psr\Http\Message;
use ReflectionObject;

use function sort;

/**
 * RequestPoolFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\RequestPoolFactory::class)]
final class RequestPoolFactoryTest extends Framework\TestCase
{
    use Tests\ClientMockTrait;

    private Src\Http\Message\RequestPoolFactory $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->subject = Src\Http\Message\RequestPoolFactory::create([
            new Psr7\Request('GET', 'https://www.example.com/'),
            new Psr7\Request('GET', 'https://www.example.com/baz'),
            new Psr7\Request('GET', 'https://www.example.com/foo'),
        ])->withClient($this->client);
    }

    #[Framework\Attributes\Test]
    public function createReturnsObjectForGivenRequests(): void
    {
        $visitedUrls = [];
        $response = function (Message\RequestInterface $request) use (&$visitedUrls) {
            $visitedUrls[] = (string) $request->getUri();

            return new Psr7\Response();
        };

        $expected = [
            'https://www.example.com/',
            'https://www.example.com/baz',
            'https://www.example.com/foo',
        ];

        $this->mockHandler->append(
            $response(...),
            $response(...),
            $response(...),
        );

        $this->subject->createPool()->promise()->wait();

        sort($visitedUrls);

        self::assertSame($expected, $visitedUrls);
    }

    #[Framework\Attributes\Test]
    public function withClientClonesObjectAndAppliesGivenClient(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $this->subject->createPool()->promise()->wait();

        self::assertNotNull($this->mockHandler->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function withConcurrencyClonesObjectAndAppliesGivenConcurrency(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $actual = $this->subject->withConcurrency(123);

        self::assertPropertyEquals($actual, 'concurrency', 123);
    }

    #[Framework\Attributes\Test]
    public function withOptionsClonesObjectAndAppliesGivenOptions(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
        );

        $actual = $this->subject->withOptions([
            RequestOptions::HEADERS => [
                'X-Foo' => 'baz',
            ],
        ]);

        $actual->createPool()->promise()->wait();

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertNotNull($lastRequest);
        self::assertSame(['baz'], $lastRequest->getHeader('X-Foo'));
    }

    #[Framework\Attributes\Test]
    public function withResponseHandlerClonesObjectAndAppliesGivenResponseHandler(): void
    {
        $handler = new Tests\Http\Message\Handler\DummyHandler();

        $this->mockHandler->append(
            new Psr7\Response(),
            new Exception(),
            new Psr7\Response(),
        );

        $actual = $this->subject->withResponseHandler($handler);

        $actual->createPool()->promise()->wait();

        self::assertCount(2, $handler->successful);
        self::assertCount(1, $handler->failed);
    }

    #[Framework\Attributes\Test]
    public function withStopOnFailureClonesObjectAndConfiguresStopOnFailure(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
            new Exception(),
        );

        $actual = $this->subject->withStopOnFailure();

        $this->expectException(Promise\CancellationException::class);

        $actual->createPool()->promise()->wait();
    }

    private function assertPropertyEquals(object $object, string $property, mixed $expected): void
    {
        $reflectionObject = new ReflectionObject($object);
        $reflectionProperty = $reflectionObject->getProperty($property);

        self::assertEquals($expected, $reflectionProperty->getValue($object));
    }
}
