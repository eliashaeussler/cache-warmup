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

use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use Psr\Http\Client;
use Psr\Http\Message;
use SimpleXMLElement;

/**
 * XmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParser
{
    private const ELEMENT_SITEMAPINDEX = 'sitemapindex';
    private const ELEMENT_URLSET = 'urlset';

    public function __construct(
        private readonly Client\ClientInterface $client = new GuzzleClient(),
    ) {
    }

    public function parse(Sitemap $sitemap): Result\ParserResult
    {
        $result = new Result\ParserResult();

        // Fetch XML source
        $request = new Psr7\Request('GET', $sitemap->getUri());
        $response = $this->client->sendRequest($request);
        $xml = new SimpleXMLElement($response->getBody()->getContents(), LIBXML_NOBLANKS);

        // Parse XML
        switch ($xml->getName()) {
            case self::ELEMENT_SITEMAPINDEX:
                foreach ($xml->sitemap as $sitemap) {
                    $parsedSitemap = $this->parseSitemap($sitemap);
                    if ($parsedSitemap instanceof Sitemap) {
                        $result->addSitemap($parsedSitemap);
                    }
                }
                break;
            case self::ELEMENT_URLSET:
                foreach ($xml->url as $url) {
                    $parsedUrl = $this->parseUrl($url);
                    if ($parsedUrl instanceof Message\UriInterface) {
                        $result->addUrl($parsedUrl);
                    }
                }
                break;
        }

        return $result;
    }

    private function parseSitemap(SimpleXMLElement $xml): ?Sitemap
    {
        if (!isset($xml->loc)) {
            return null;
        }

        $sitemapUri = $xml->loc[0];
        $sitemapUri = new Psr7\Uri((string) $sitemapUri);

        return new Sitemap($sitemapUri);
    }

    private function parseUrl(SimpleXMLElement $xml): ?Message\UriInterface
    {
        if (!isset($xml->loc)) {
            return null;
        }

        $uri = $xml->loc[0];

        return new Psr7\Uri((string) $uri);
    }
}
