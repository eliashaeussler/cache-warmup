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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Xml;

use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;
use SimpleXMLElement;

/**
 * XmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParser
{
    public const ELEMENT_SITEMAPINDEX = 'sitemapindex';
    public const ELEMENT_URLSET = 'urlset';

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Sitemap[]
     */
    private $parsedSitemaps = [];

    /**
     * @var UriInterface[]
     */
    private $parsedUrls = [];

    public function __construct(Sitemap $sitemap, ClientInterface $client = null)
    {
        $this->sitemap = $sitemap;
        $this->client = $client ?? new Client();
    }

    public function parse(): void
    {
        // Fetch XML source
        $request = new Request('GET', $this->sitemap->getUri());
        if ($this->client instanceof \GuzzleHttp\ClientInterface) {
            // Make sure redirects and errors are properly handled when using a Guzzle client
            $response = $this->client->send($request);
        } else {
            $response = $this->client->sendRequest($request);
        }
        $xml = new SimpleXMLElement($response->getBody()->getContents(), LIBXML_NOBLANKS);

        // Parse XML
        switch ($xml->getName()) {
            case self::ELEMENT_SITEMAPINDEX:
                foreach ($xml->sitemap as $sitemap) {
                    $parsedSitemap = $this->parseSitemap($sitemap);
                    if ($parsedSitemap instanceof Sitemap) {
                        $this->parsedSitemaps[] = $parsedSitemap;
                    }
                }
                break;
            case self::ELEMENT_URLSET:
                foreach ($xml->url as $url) {
                    $parsedUrl = $this->parseUrl($url);
                    if ($parsedUrl instanceof UriInterface) {
                        $this->parsedUrls[] = $parsedUrl;
                    }
                }
                break;
        }
    }

    /**
     * @return Sitemap[]
     */
    public function getParsedSitemaps(): array
    {
        return $this->parsedSitemaps;
    }

    /**
     * @return UriInterface[]
     */
    public function getParsedUrls(): array
    {
        return $this->parsedUrls;
    }

    private function parseSitemap(SimpleXMLElement $xml): ?Sitemap
    {
        if (!isset($xml->loc)) {
            return null;
        }

        $sitemapUri = $xml->loc[0];
        $sitemapUri = new Uri((string) $sitemapUri);

        return new Sitemap($sitemapUri);
    }

    private function parseUrl(SimpleXMLElement $xml): ?UriInterface
    {
        if (!isset($xml->loc)) {
            return null;
        }

        $uri = $xml->loc[0];

        return new Uri((string) $uri);
    }
}
