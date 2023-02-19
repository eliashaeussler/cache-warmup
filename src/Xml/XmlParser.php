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

namespace EliasHaeussler\CacheWarmup\Xml;

use CuyZ\Valinor;
use DateTimeInterface;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Mapper;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use Psr\Http\Message;
use Throwable;

/**
 * XmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParser
{
    private readonly Valinor\Mapper\TreeMapper $mapper;

    public function __construct(
        private readonly ClientInterface $client = new Client(),
    ) {
        $this->mapper = $this->createMapper();
    }

    /**
     * @throws Exception\InvalidSitemapException
     * @throws Exception\MalformedXmlException
     * @throws GuzzleException
     */
    public function parse(Sitemap\Sitemap $sitemap): Result\ParserResult
    {
        // Fetch XML source
        $request = new Psr7\Request('GET', $sitemap->getUri());
        $response = $this->client->send($request);
        $body = (string) $response->getBody();

        // Initialize XML source
        $xml = Mapper\Source\XmlSource::fromXml($body)
            ->asCollection('sitemap')
            ->asCollection('url');
        $source = Valinor\Mapper\Source\Source::iterable($xml)->map([
            'sitemap' => 'sitemaps',
            'sitemap.*.loc' => 'uri',
            'sitemap.*.lastmod' => 'lastModificationDate',
            'url' => 'urls',
            'url.*.loc' => 'uri',
            'url.*.lastmod' => 'lastModificationDate',
            'url.*.changefreq' => 'changeFrequency',
        ]);

        // Map XML source
        try {
            return $this->mapper->map(Result\ParserResult::class, $source);
        } catch (Valinor\Mapper\MappingError $error) {
            throw Exception\InvalidSitemapException::create($sitemap, $error);
        }
    }

    private function createMapper(): Valinor\Mapper\TreeMapper
    {
        return (new Valinor\MapperBuilder())
            ->registerConstructor(
                Sitemap\ChangeFrequency::fromCaseInsensitive(...),
            )
            ->infer(Message\UriInterface::class, static fn () => Psr7\Uri::class)
            ->enableFlexibleCasting()
            ->allowSuperfluousKeys()
            ->filterExceptions(
                static function (Throwable $exception) {
                    if ($exception instanceof Exception\InvalidUrlException) {
                        return Valinor\Mapper\Tree\Message\MessageBuilder::from($exception);
                    }

                    throw $exception;
                },
            )
            ->supportDateFormats(DateTimeInterface::W3C)
            ->mapper()
        ;
    }
}
