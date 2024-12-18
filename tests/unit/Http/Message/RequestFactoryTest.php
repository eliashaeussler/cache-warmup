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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Message;

use EliasHaeussler\CacheWarmup as Src;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework;

use function iterator_to_array;

/**
 * RequestFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\RequestFactory::class)]
final class RequestFactoryTest extends Framework\TestCase
{
    private Src\Http\Message\RequestFactory $subject;

    public function setUp(): void
    {
        $this->subject = new Src\Http\Message\RequestFactory(
            'GET',
            [
                'X-Foo' => 'Baz',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function buildReturnsRequestWithDefaultUserAgentIncluded(): void
    {
        $uri = new Uri('https://example.com/');
        $actual = $this->subject->build($uri);

        self::assertSame('GET', $actual->getMethod());
        self::assertSame($uri, $actual->getUri());
        self::assertSame(['Baz'], $actual->getHeader('X-Foo'));
        self::assertStringStartsWith('EliasHaeussler-CacheWarmup/', $actual->getHeader('User-Agent')[0] ?? '');
    }

    #[Framework\Attributes\Test]
    public function buildReturnsRequestWithCustomUserAgent(): void
    {
        $uri = new Uri('https://example.com/');
        $subject = new Src\Http\Message\RequestFactory(
            'GET',
            [
                'User-Agent' => 'foo',
            ],
        );
        $actual = $subject->build($uri);

        self::assertSame(['foo'], $actual->getHeader('User-Agent'));
    }

    #[Framework\Attributes\Test]
    public function buildIterableReturnsGeneratorWithRequests(): void
    {
        $url1 = new Uri('https://example.com/');
        $url2 = new Uri('https://example.com/de/');
        $url3 = new Uri('https://example.com/fr/');
        $urls = [$url1, $url2, $url3];

        $actual = iterator_to_array($this->subject->buildIterable($urls));

        self::assertCount(3, $actual);
        self::assertSame($url1, $actual[0]->getUri());
        self::assertSame($url2, $actual[1]->getUri());
        self::assertSame($url3, $actual[2]->getUri());
    }
}
