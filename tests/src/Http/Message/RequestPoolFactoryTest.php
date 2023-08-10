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
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;

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

    protected function setUp(): void
    {
        $this->client = $this->createClient();
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

        $actual = Src\Http\Message\RequestPoolFactory::create([
            new Psr7\Request('GET', 'https://www.example.com/'),
            new Psr7\Request('GET', 'https://www.example.com/foo'),
            new Psr7\Request('GET', 'https://www.example.com/baz'),
        ])->withClient($this->client);

        $this->mockHandler->append(
            $response(...),
            $response(...),
            $response(...),
        );

        $actual->createPool()->promise()->wait();

        sort($visitedUrls);

        self::assertSame($expected, $visitedUrls);
    }
}
