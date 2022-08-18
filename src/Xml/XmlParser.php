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

namespace EliasHaeussler\CacheWarmup\Xml;

use DateTimeInterface;
use Doctrine\Common\Annotations;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Normalizer;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use Psr\Http\Client;
use Symfony\Component\PropertyInfo;
use Symfony\Component\Serializer;

/**
 * XmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParser
{
    public function __construct(
        private readonly Client\ClientInterface $client = new GuzzleClient(),
    ) {
    }

    /**
     * @throws Exception\InvalidSitemapException
     * @throws Exception\InvalidUrlException
     */
    public function parse(Sitemap\Sitemap $sitemap): Result\ParserResult
    {
        // Fetch XML source
        $request = new Psr7\Request('GET', $sitemap->getUri());
        $response = $this->client->sendRequest($request);
        $body = (string) $response->getBody();

        // Deserialize XML
        $serializer = new Serializer\Serializer(
            [
                new Normalizer\UriDenormalizer(),
                new Serializer\Normalizer\ArrayDenormalizer(),
                new Serializer\Normalizer\DateTimeNormalizer([
                    Serializer\Normalizer\DateTimeNormalizer::FORMAT_KEY => DateTimeInterface::W3C,
                ]),
                new Serializer\Normalizer\BackedEnumNormalizer(),
                new Serializer\Normalizer\ObjectNormalizer(
                    $classMetadataFactory = new Serializer\Mapping\Factory\ClassMetadataFactory(
                        new Serializer\Mapping\Loader\AnnotationLoader(new Annotations\AnnotationReader())
                    ),
                    nameConverter: new Serializer\NameConverter\MetadataAwareNameConverter($classMetadataFactory),
                    propertyTypeExtractor: new PropertyInfo\Extractor\PhpDocExtractor(),
                ),
            ],
            [
                new Serializer\Encoder\XmlEncoder(),
            ],
        );

        try {
            return $serializer->deserialize($body, Result\ParserResult::class, 'xml');
        } catch (Serializer\Exception\ExceptionInterface $exception) {
            throw Exception\InvalidSitemapException::create($sitemap, $exception);
        }
    }
}
