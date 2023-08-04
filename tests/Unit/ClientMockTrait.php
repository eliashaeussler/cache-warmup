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

namespace EliasHaeussler\CacheWarmup\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;

/**
 * ClientMockTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait ClientMockTrait
{
    protected Handler\MockHandler $mockHandler;
    protected Client $client;

    /**
     * @var array<string, Psr7\Stream>
     */
    protected array $streams = [];

    protected function mockSitemapRequest(string $fixture, string $extension = 'xml'): void
    {
        $fixtureFile = __DIR__.'/Fixtures/'.$fixture.'.'.$extension;

        self::assertFileExists($fixtureFile);

        $stream = $this->openStream($fixtureFile);

        $this->mockHandler->append(new Psr7\Response(body: $stream));
    }

    protected function createClient(): Client
    {
        $this->mockHandler = new Handler\MockHandler();

        return new Client(['handler' => HandlerStack::create($this->mockHandler)]);
    }

    /**
     * @param non-empty-string $file
     */
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
