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

namespace EliasHaeussler\CacheWarmup;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function array_key_exists;
use function array_values;
use function count;
use function fnmatch;
use function is_array;
use function is_string;
use function preg_match;
use function str_contains;
use function str_starts_with;

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

    /**
     * @var list<Sitemap\Sitemap>
     */
    private array $excludedSitemaps = [];

    /**
     * @var list<Sitemap\Url>
     */
    private array $excludedUrls = [];

    /**
     * @param array<string> $excludePatterns
     */
    public function __construct(
        private readonly int $limit = 0,
        private readonly ClientInterface $client = new Client(),
        private readonly Crawler\CrawlerInterface $crawler = new Crawler\ConcurrentCrawler(),
        private readonly ?Crawler\Strategy\CrawlingStrategy $strategy = null,
        private readonly bool $strict = true,
        private readonly array $excludePatterns = [],
    ) {
        $this->parser = new Xml\XmlParser($this->client);
    }

    public function run(): Result\CacheWarmupResult
    {
        $urls = $this->getUrls();

        if (null !== $this->strategy) {
            $urls = $this->strategy->prepareUrls($urls);
        }

        return $this->crawler->crawl($urls);
    }

    /**
     * @param list<string|Sitemap\Sitemap>|string|Sitemap\Sitemap $sitemaps
     *
     * @throws Exception\FilesystemFailureException
     * @throws Exception\InvalidSitemapException
     * @throws Exception\InvalidUrlException
     * @throws Exception\MalformedXmlException
     * @throws GuzzleException
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
                $sitemapUri = $this->resolveSitemapUri($sitemap);
                $sitemap = new Sitemap\Sitemap($sitemapUri);
            }

            // Throw exception if sitemap is invalid
            if (!($sitemap instanceof Sitemap\Sitemap)) {
                throw Exception\InvalidSitemapException::forInvalidType($sitemap);
            }

            // Skip sitemap if exclude pattern matches
            if ($this->isExcluded((string) $sitemap->getUri())) {
                $this->excludedSitemaps[] = $sitemap;

                continue;
            }

            // Parse sitemap object
            try {
                $result = $this->parser->parse($sitemap);
            } catch (GuzzleException|Exception\FilesystemFailureException|Exception\InvalidSitemapException|Exception\MalformedXmlException $exception) {
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

        if ($this->isExcluded((string) $url)) {
            $this->excludedUrls[] = $url;

            return $this;
        }

        if (!$this->exceededLimit() && !array_key_exists((string) $url, $this->urls)) {
            $this->urls[(string) $url] = $url;
        }

        return $this;
    }

    private function resolveSitemapUri(string $sitemap): Message\UriInterface
    {
        // Sitemap is a remote URL
        if (str_contains($sitemap, '://')) {
            return new Psr7\Uri($sitemap);
        }

        // Sitemap is a local file
        $file = Helper\FilesystemHelper::resolveRelativePath($sitemap);

        return new Psr7\Uri('file://'.$file);
    }

    private function exceededLimit(): bool
    {
        return $this->limit > 0 && count($this->urls) >= $this->limit;
    }

    private function isExcluded(string $url): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (fnmatch($pattern, $url)) {
                return true;
            }

            if (str_starts_with($pattern, '#') && 1 === preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
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

    /**
     * @return list<Sitemap\Sitemap>
     */
    public function getExcludedSitemaps(): array
    {
        return $this->excludedSitemaps;
    }

    /**
     * @return list<Sitemap\Url>
     */
    public function getExcludedUrls(): array
    {
        return $this->excludedUrls;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
