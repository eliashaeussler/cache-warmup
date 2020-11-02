<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Http;

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

use EliasHaeussler\CacheWarmup\Exception\ClientException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 compliant implementation of Guzzle HTTP Client.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 * @codeCoverageIgnore
 */
class Client extends GuzzleClient implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->send($request);
        } catch (GuzzleException $e) {
            if (method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
            }
            throw new ClientException($e->getMessage(), $request, $response ?? null, $e);
        }
    }
}
