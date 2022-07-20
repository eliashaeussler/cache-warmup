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

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use GuzzleHttp\Psr7;
use Prophecy\Argument;
use Prophecy\Prophecy;
use Psr\Http\Client;
use Psr\Http\Message;

/**
 * RequestProphecyTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait RequestProphecyTrait
{
    /**
     * @var Prophecy\ObjectProphecy|Client\ClientInterface
     */
    protected Prophecy\ObjectProphecy $clientProphecy;

    /**
     * @var array<string, Psr7\Stream>
     */
    protected array $streams = [];

    /**
     * @throws Client\ClientExceptionInterface
     */
    protected function prophesizeSitemapRequest(string $fixture, Message\UriInterface $expectedUri = null): void
    {
        $fixtureFile = __DIR__.'/Fixtures/'.$fixture.'.xml';

        self::assertFileExists($fixtureFile);

        $stream = $this->openStream($fixtureFile);

        /* @noinspection PhpParamsInspection */
        /* @noinspection PhpUndefinedMethodInspection */
        $this->clientProphecy
            ->sendRequest(
                Argument::that(function (Psr7\Request $request) use ($expectedUri) {
                    if (null === $expectedUri) {
                        return true;
                    }

                    return (string) $request->getUri() === (string) $expectedUri;
                })
            )
            ->willReturn(new Psr7\Response(200, body: $stream))
            ->shouldBeCalled()
        ;
    }

    protected function openStream(string $file): Psr7\Stream
    {
        if (isset($this->streams[$file])) {
            return $this->streams[$file];
        }

        self::assertFileExists($file);

        $resource = fopen($file, 'r');

        self::assertIsResource($resource);

        $stream = new Psr7\Stream($resource);
        $this->streams[$file] = $stream;

        return $stream;
    }

    protected function closeStreams(): void
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }
    }
}
