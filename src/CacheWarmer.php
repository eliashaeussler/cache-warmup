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
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher;

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
    public const VERSION = '3.2.2';

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
     * @param list<Config\Option\ExcludePattern> $excludePatterns
     */
    public function __construct(
        private readonly int $limit = 0,
        private readonly ClientInterface $client = new Client(),
        private readonly Crawler\Crawler $crawler = new Crawler\ConcurrentCrawler(),
        private readonly ?Crawler\Strategy\CrawlingStrategy $strategy = null,
        private readonly bool $strict = true,
        private readonly array $excludePatterns = [],
        private readonly EventDispatcherInterface $eventDispatcher = new EventDispatcher\EventDispatcher(),
    ) {
        $this->parser = new Xml\XmlParser($this->client);
    }

    public function run(): Result\CacheWarmupResult
    {
        $urls = $this->getUrls();

        if (null !== $this->strategy) {
            $urls = $this->strategy->prepareUrls($urls);
            $this->eventDispatcher->dispatch(new Event\UrlsPrepared($this->strategy, $urls));
        }

        $this->eventDispatcher->dispatch(new Event\CrawlingStarted($urls, $this->crawler));
        $result = $this->crawler->crawl($urls);
        $this->eventDispatcher->dispatch(new Event\CrawlingFinished($urls, $this->crawler, $result));

        return $result;
    }

    /**
     * @param list<string|Sitemap\Sitemap>|string|Sitemap\Sitemap $sitemaps
     *
     * @throws Exception\Exception
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
                $sitemap = Sitemap\Sitemap::createFromString($sitemap);
            }

            // Throw exception if sitemap is invalid
            if (!($sitemap instanceof Sitemap\Sitemap)) {
                throw new Exception\SitemapIsInvalid($sitemap);
            }

            // Skip sitemap if exclude pattern matches
            if ($this->isExcluded((string) $sitemap->getUri())) {
                $this->excludedSitemaps[] = $sitemap;
                $this->eventDispatcher->dispatch(new Event\SitemapExcluded($sitemap));

                continue;
            }

            // Parse sitemap object
            try {
                $result = $this->parser->parse($sitemap);
                $this->eventDispatcher->dispatch(new Event\SitemapParsed($sitemap, $result));
            } catch (GuzzleException|Exception\Exception $exception) {
                $this->eventDispatcher->dispatch(new Event\SitemapParsingFailed($sitemap, $exception));

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
            $this->eventDispatcher->dispatch(new Event\SitemapAdded($sitemap));
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
            $this->eventDispatcher->dispatch(new Event\UrlExcluded($url));

            return $this;
        }

        if (!$this->exceededLimit() && !array_key_exists((string) $url, $this->urls)) {
            $this->urls[(string) $url] = $url;
            $this->eventDispatcher->dispatch(new Event\UrlAdded($url));
        }

        return $this;
    }

    private function exceededLimit(): bool
    {
        return $this->limit > 0 && count($this->urls) >= $this->limit;
    }

    private function isExcluded(string $url): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if ($pattern->matches($url)) {
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
