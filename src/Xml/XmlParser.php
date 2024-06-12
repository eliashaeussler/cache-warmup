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

use function file_exists;
use function file_get_contents;
use function is_readable;
use function str_starts_with;
use function substr;

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
     * @throws Exception\FileIsMissing
     * @throws Exception\XmlIsMalformed
     * @throws Exception\SitemapCannotBeParsed
     * @throws GuzzleException
     */
    public function parse(Sitemap\Sitemap $sitemap): Result\ParserResult
    {
        $uri = $sitemap->getUri();

        // Fetch XML source
        if (str_starts_with((string) $uri, 'file://')) {
            $contents = $this->fetchLocalFile($uri);
        } else {
            $contents = $this->fetchUrl($uri);
        }

        // Decode gzipped sitemap
        if (0 === mb_strpos($contents, "\x1f\x8b\x08")) {
            $contents = (string) gzdecode($contents);
        }

        // Initialize XML source
        $xml = Mapper\Source\XmlSource::fromXml($contents)
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
            $result = $this->mapper->map(Result\ParserResult::class, $source);
        } catch (Valinor\Mapper\MappingError $error) {
            throw new Exception\SitemapCannotBeParsed($sitemap, $error);
        }

        // Apply origin to sitemaps and urls
        foreach ($result->getSitemaps() as $parsedSitemap) {
            $parsedSitemap->setOrigin($sitemap);
        }
        foreach ($result->getUrls() as $parsedUrl) {
            $parsedUrl->setOrigin($sitemap);
        }

        return $result;
    }

    /**
     * @throws Exception\FileIsMissing
     */
    private function fetchLocalFile(Message\UriInterface $uri): string
    {
        // Remove file:// prefix
        $filename = substr((string) $uri, 7);

        if (!file_exists($filename) || !is_readable($filename)) {
            throw new Exception\FileIsMissing($filename);
        }

        return (string) file_get_contents($filename);
    }

    /**
     * @throws GuzzleException
     */
    private function fetchUrl(Message\UriInterface $uri): string
    {
        $request = new Psr7\Request('GET', $uri);
        $response = $this->client->send($request);

        return (string) $response->getBody();
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
                    if ($exception instanceof Exception\UrlIsEmpty || $exception instanceof Exception\UrlIsInvalid) {
                        return Valinor\Mapper\Tree\Message\MessageBuilder::from($exception);
                    }

                    throw $exception;
                },
            )
            ->supportDateFormats(
                DateTimeInterface::W3C,
                'Y-m-d\TH:i:s.v\Z',
                '!Y-m-d',
            )
            ->mapper()
        ;
    }
}
