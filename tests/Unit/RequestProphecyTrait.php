<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Tests\Unit;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Uri;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * RequestProphecyTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait RequestProphecyTrait
{
    /**
     * @var ObjectProphecy|ClientInterface
     */
    protected $clientProphecy;

    /**
     * @var resource|null
     */
    protected $stream;

    /**
     * @param string $fixture
     * @param Uri|null $expectedUri
     * @throws ClientExceptionInterface
     */
    protected function prophesizeSitemapRequest(string $fixture, Uri $expectedUri = null): void
    {
        $absolutePath = 'Fixtures/' . $fixture . '.xml';
        $fixtureFile = realpath(__DIR__ . '/' . $absolutePath);
        if (!$fixtureFile) {
            $fixtureFile =  realpath(__DIR__ . '/../' . $absolutePath);
        }

        $this->openStream($fixtureFile);
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->clientProphecy
            ->sendRequest(
                Argument::that(function (Request $request) use ($expectedUri) {
                    if ($expectedUri === null) {
                        return true;
                    }
                    return (string)$request->getUri() === (string)$expectedUri;
                })
            )
            ->willReturn(new Response(200, [], $this->stream));
    }

    protected function openStream(string $file): void
    {
        $this->closeStream();
        $this->stream = new Stream(fopen($file, 'r'));
    }

    protected function closeStream(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
