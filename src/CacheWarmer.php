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

namespace EliasHaeussler\CacheWarmup;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use Psr\Http\Client;
use Psr\Http\Message;

use function count;
use function in_array;
use function is_array;
use function is_string;

/**
 * CacheWarmer.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmer
{
    private readonly Xml\XmlParser $parser;

    /**
     * @var list<Message\UriInterface>
     */
    private array $urls = [];

    /**
     * @var list<Sitemap>
     */
    private array $sitemaps = [];

    public function __construct(
        private readonly int $limit = 0,
        private readonly Client\ClientInterface $client = new GuzzleClient(),
        private readonly Crawler\CrawlerInterface $crawler = new Crawler\ConcurrentCrawler(),
    ) {
        $this->parser = new Xml\XmlParser($this->client);
    }

    public function run(): Result\CacheWarmupResult
    {
        return $this->crawler->crawl($this->urls);
    }

    /**
     * @param list<string|Sitemap>|string|Sitemap $sitemaps
     *
     * @throws Exception\InvalidSitemapException
     */
    public function addSitemaps(array|string|Sitemap $sitemaps): self
    {
        // Early return if no more URLs should be added
        if ($this->exceededLimit()) {
            return $this;
        }

        // Force array of sitemaps to be parsed
        if (!is_array($sitemaps)) {
            $sitemaps = [$sitemaps];
        }

        // Validate and parse given sitemaps
        foreach ($sitemaps as $sitemap) {
            // Parse sitemap URL to valid sitemap object
            if (is_string($sitemap)) {
                $sitemap = new Sitemap(new Psr7\Uri($sitemap));
            }

            // Throw exception if sitemap is invalid
            if (!($sitemap instanceof Sitemap)) {
                throw Exception\InvalidSitemapException::forInvalidType($sitemap);
            }

            // Parse sitemap object
            $this->addSitemap($sitemap);
            $result = $this->parser->parse($sitemap);

            foreach ($result->getSitemaps() as $parsedSitemap) {
                $this->addSitemaps($parsedSitemap);
            }

            foreach ($result->getUrls() as $parsedUrl) {
                $this->addUrl($parsedUrl);
            }
        }

        return $this;
    }

    private function addSitemap(Sitemap $sitemap): self
    {
        if (!in_array($sitemap, $this->sitemaps, true)) {
            $this->sitemaps[] = $sitemap;
        }

        return $this;
    }

    public function addUrl(Message\UriInterface $url): self
    {
        if (!$this->exceededLimit() && !in_array($url, $this->urls, true)) {
            $this->urls[] = $url;
        }

        return $this;
    }

    private function exceededLimit(): bool
    {
        return $this->limit > 0 && count($this->urls) >= $this->limit;
    }

    /**
     * @return list<Message\UriInterface>
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @return list<Sitemap>
     */
    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
