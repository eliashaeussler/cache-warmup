<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler;
use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\CacheWarmup\Xml\XmlParser;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;

use function count;
use function gettype;
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
    /**
     * @var UriInterface[]
     */
    private $urls = [];

    /**
     * @var Sitemap[]
     */
    private $sitemaps = [];

    /**
     * @var int
     */
    private $limit;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param string[]|Sitemap[]|string|Sitemap|null $sitemaps
     */
    public function __construct($sitemaps = null, int $limit = 0, ?ClientInterface $client = null)
    {
        $this->limit = $limit;
        $this->client = $client ?? new Client();
        $this->addSitemaps($sitemaps);
    }

    public function run(?CrawlerInterface $crawler = null): CrawlerInterface
    {
        $crawler = $crawler ?? new ConcurrentCrawler();
        $crawler->crawl($this->urls);

        return $crawler;
    }

    /**
     * @param string[]|Sitemap[]|Sitemap|string|null $sitemaps
     */
    public function addSitemaps($sitemaps): self
    {
        // Early return if no more URLs should be added
        if ($this->exceededLimit()) {
            return $this;
        }

        // Early return if no sitemaps are given
        if (null === $sitemaps) {
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
                $this->validateSitemapUrl($sitemap);
                $sitemap = new Sitemap(new Uri($sitemap));
            }
            // Parse sitemap object
            if ($sitemap instanceof Sitemap) {
                $this->addSitemap($sitemap);
                $parser = new XmlParser($sitemap, $this->client);
                $parser->parse();
                foreach ($parser->getParsedSitemaps() as $parsedSitemap) {
                    $this->addSitemaps($parsedSitemap);
                }
                foreach ($parser->getParsedUrls() as $parsedUrl) {
                    $this->addUrl($parsedUrl);
                }
            } else {
                throw new InvalidArgumentException(sprintf('Sitemaps must be of type string or %s, %s given.', Sitemap::class, gettype($sitemap)), 1604055096);
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

    public function addUrl(UriInterface $url): self
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
     * @return UriInterface[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @return Sitemap[]
     */
    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    private function validateSitemapUrl(string $url): void
    {
        if ('' === trim($url)) {
            throw new InvalidArgumentException('Sitemap URL must not be empty.', 1604055264);
        }
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Sitemap must be a valid URL.', 1604055334);
        }
    }
}
