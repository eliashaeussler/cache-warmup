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

namespace EliasHaeussler\CacheWarmup;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use Psr\Http\Client;

use function array_key_exists;
use function array_values;
use function count;
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
     * @var array<string, Sitemap\Url>
     */
    private array $urls = [];

    /**
     * @var array<string, Sitemap\Sitemap>
     */
    private array $sitemaps = [];

    /**
     * @var list<Sitemap\Sitemap>
     */
    private array $failedSitemaps = [];

    public function __construct(
        private readonly int $limit = 0,
        private readonly Client\ClientInterface $client = new GuzzleClient(),
        private readonly Crawler\CrawlerInterface $crawler = new Crawler\ConcurrentCrawler(),
        private readonly bool $strict = true,
    ) {
        $this->parser = new Xml\XmlParser($this->client);
    }

    public function run(): Result\CacheWarmupResult
    {
        return $this->crawler->crawl($this->getUrls());
    }

    /**
     * @param list<string|Sitemap\Sitemap>|string|Sitemap\Sitemap $sitemaps
     *
     * @throws Exception\InvalidSitemapException
     * @throws Exception\InvalidUrlException
     */
    public function addSitemaps(array|string|Sitemap\Sitemap $sitemaps): self
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
                $sitemap = new Sitemap\Sitemap(new Psr7\Uri($sitemap));
            }

            // Throw exception if sitemap is invalid
            /* @phpstan-ignore-next-line */
            if (!($sitemap instanceof Sitemap\Sitemap)) {
                throw Exception\InvalidSitemapException::forInvalidType($sitemap);
            }

            // Parse sitemap object
            try {
                $result = $this->parser->parse($sitemap);
            } catch (Client\ClientExceptionInterface|Exception\Exception $exception) {
                // Exit early if running in strict mode
                if ($this->strict) {
                    throw $exception;
                }

                $this->failedSitemaps[] = $sitemap;

                continue;
            }

            $this->addSitemap($sitemap);

            foreach ($result->getSitemaps() as $parsedSitemap) {
                $this->addSitemaps($parsedSitemap);
            }

            foreach ($result->getUrls() as $parsedUrl) {
                $this->addUrl($parsedUrl);
            }
        }

        return $this;
    }

    private function addSitemap(Sitemap\Sitemap $sitemap): self
    {
        if (!array_key_exists((string) $sitemap, $this->sitemaps)) {
            $this->sitemaps[(string) $sitemap] = $sitemap;
        }

        return $this;
    }

    public function addUrl(string|Sitemap\Url $url): self
    {
        if (is_string($url)) {
            $url = new Sitemap\Url($url);
        }

        if (!$this->exceededLimit() && !array_key_exists((string) $url, $this->urls)) {
            $this->urls[(string) $url] = $url;
        }

        return $this;
    }

    private function exceededLimit(): bool
    {
        return $this->limit > 0 && count($this->urls) >= $this->limit;
    }

    /**
     * @return list<Sitemap\Url>
     */
    public function getUrls(): array
    {
        return array_values($this->urls);
    }

    /**
     * @return list<Sitemap\Sitemap>
     */
    public function getSitemaps(): array
    {
        return array_values($this->sitemaps);
    }

    /**
     * @return list<Sitemap\Sitemap>
     */
    public function getFailedSitemaps(): array
    {
        return $this->failedSitemaps;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
