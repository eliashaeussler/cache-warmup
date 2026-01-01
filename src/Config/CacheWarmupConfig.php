<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Config;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Formatter;
use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\CacheWarmup\Xml;
use Psr\Log;
use ReflectionClass;

use function array_key_exists;
use function get_object_vars;
use function is_string;
use function max;
use function property_exists;

/**
 * CacheWarmupConfig.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmupConfig
{
    /**
     * @param list<Sitemap\Sitemap>                              $sitemaps
     * @param list<Sitemap\Url>                                  $urls
     * @param list<Option\ExcludePattern>                        $excludePatterns
     * @param int<0, max>                                        $limit
     * @param array<string, mixed>                               $clientOptions
     * @param Crawler\Crawler|class-string<Crawler\Crawler>|null $crawler
     * @param array<string, mixed>                               $crawlerOptions
     * @param Xml\Parser|class-string<Xml\Parser>|null           $parser
     * @param array<string, mixed>                               $parserOptions
     * @param int<0, max>                                        $repeatAfter
     */
    public function __construct(
        private array $sitemaps = [],
        private array $urls = [],
        private array $excludePatterns = [],
        private int $limit = 0,
        private bool $progress = false,
        private array $clientOptions = [],
        private Crawler\Crawler|string|null $crawler = null,
        private array $crawlerOptions = [],
        private Crawler\Strategy\CrawlingStrategy|string|null $strategy = null,
        private Xml\Parser|string|null $parser = null,
        private array $parserOptions = [],
        private string $format = 'text',
        private ?string $logFile = null,
        private string $logLevel = Log\LogLevel::ERROR,
        private bool $allowFailures = false,
        private bool $stopOnFailure = false,
        private int $repeatAfter = 0,
    ) {}

    /**
     * @return list<Sitemap\Sitemap>
     */
    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    /**
     * @param list<Sitemap\Sitemap> $sitemaps
     */
    public function setSitemaps(array $sitemaps): self
    {
        $this->sitemaps = $sitemaps;

        return $this;
    }

    public function addSitemap(Sitemap\Sitemap|string $sitemap): self
    {
        if (is_string($sitemap)) {
            $sitemap = Sitemap\Sitemap::createFromString($sitemap);
        }

        $this->sitemaps[] = $sitemap;

        return $this;
    }

    /**
     * @return list<Sitemap\Url>
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param list<Sitemap\Url> $urls
     */
    public function setUrls(array $urls): self
    {
        $this->urls = $urls;

        return $this;
    }

    public function addUrl(Sitemap\Url|string $url): self
    {
        if (is_string($url)) {
            $url = new Sitemap\Url($url);
        }

        $this->urls[] = $url;

        return $this;
    }

    /**
     * @return list<Option\ExcludePattern>
     */
    public function getExcludePatterns(): array
    {
        return $this->excludePatterns;
    }

    /**
     * @param list<Option\ExcludePattern> $excludePatterns
     */
    public function setExcludePatterns(array $excludePatterns): self
    {
        $this->excludePatterns = $excludePatterns;

        return $this;
    }

    public function addExcludePattern(Option\ExcludePattern $excludePattern): self
    {
        $this->excludePatterns[] = $excludePattern;

        return $this;
    }

    /**
     * @return non-negative-int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param non-negative-int $limit
     */
    public function setLimit(int $limit): self
    {
        $this->limit = max($limit, 0);

        return $this;
    }

    public function disableLimit(): self
    {
        $this->limit = 0;

        return $this;
    }

    public function isProgressBarEnabled(): bool
    {
        return $this->progress;
    }

    public function enableProgressBar(): self
    {
        $this->progress = true;

        return $this;
    }

    public function disableProgressBar(): self
    {
        $this->progress = false;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClientOptions(): array
    {
        return $this->clientOptions;
    }

    /**
     * @param array<string, mixed> $clientOptions
     */
    public function setClientOptions(array $clientOptions): self
    {
        $this->clientOptions = $clientOptions;

        return $this;
    }

    public function setClientOption(string $name, mixed $value): self
    {
        $this->clientOptions[$name] = $value;

        return $this;
    }

    public function removeClientOption(string $name): self
    {
        unset($this->clientOptions[$name]);

        return $this;
    }

    /**
     * @return Crawler\Crawler|class-string<Crawler\Crawler>|null
     */
    public function getCrawler(): Crawler\Crawler|string|null
    {
        return $this->crawler;
    }

    /**
     * @param Crawler\Crawler|class-string<Crawler\Crawler> $crawler
     */
    public function setCrawler(Crawler\Crawler|string $crawler): self
    {
        $this->crawler = $crawler;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCrawlerOptions(): array
    {
        return $this->crawlerOptions;
    }

    /**
     * @param array<string, mixed> $crawlerOptions
     */
    public function setCrawlerOptions(array $crawlerOptions): self
    {
        $this->crawlerOptions = $crawlerOptions;

        return $this;
    }

    public function setCrawlerOption(string $name, mixed $value): self
    {
        $this->crawlerOptions[$name] = $value;

        return $this;
    }

    public function removeCrawlerOption(string $name): self
    {
        unset($this->crawlerOptions[$name]);

        return $this;
    }

    public function getStrategy(): Crawler\Strategy\CrawlingStrategy|string|null
    {
        return $this->strategy;
    }

    public function setStrategy(Crawler\Strategy\CrawlingStrategy|string $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return Xml\Parser|class-string<Xml\Parser>|null
     */
    public function getParser(): Xml\Parser|string|null
    {
        return $this->parser;
    }

    /**
     * @param Xml\Parser|class-string<Xml\Parser> $parser
     */
    public function setParser(Xml\Parser|string $parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParserOptions(): array
    {
        return $this->parserOptions;
    }

    /**
     * @param array<string, mixed> $parserOptions
     */
    public function setParserOptions(array $parserOptions): self
    {
        $this->parserOptions = $parserOptions;

        return $this;
    }

    public function setParserOption(string $name, mixed $value): self
    {
        $this->parserOptions[$name] = $value;

        return $this;
    }

    public function removeParserOption(string $name): self
    {
        unset($this->parserOptions[$name]);

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function useJsonFormat(): self
    {
        return $this->setFormat(Formatter\JsonFormatter::getType());
    }

    public function useTextFormat(): self
    {
        return $this->setFormat(Formatter\TextFormatter::getType());
    }

    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function setLogLevel(string $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    public function areFailuresAllowed(): bool
    {
        return $this->allowFailures;
    }

    public function allowFailures(): self
    {
        $this->allowFailures = true;

        return $this;
    }

    public function disallowFailures(): self
    {
        $this->allowFailures = false;

        return $this;
    }

    public function shouldStopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function stopOnFailure(): self
    {
        $this->stopOnFailure = true;

        return $this;
    }

    public function dontStopOnFailure(): self
    {
        $this->stopOnFailure = false;

        return $this;
    }

    /**
     * @return non-negative-int
     */
    public function getRepeatAfter(): int
    {
        return $this->repeatAfter;
    }

    /**
     * @param non-negative-int $seconds
     */
    public function repeatAfter(int $seconds): self
    {
        $this->repeatAfter = max($seconds, 0);

        return $this;
    }

    public function disableEndlessMode(): self
    {
        $this->repeatAfter = 0;

        return $this;
    }

    public function merge(self $other): self
    {
        $parameters = $this->toArray(true);

        Helper\ArrayHelper::mergeRecursive($parameters, $other->toArray(true));

        foreach ($parameters as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $omitDefaultValues = false): array
    {
        $config = get_object_vars($this);

        if (!$omitDefaultValues) {
            return $config;
        }

        $reflection = new ReflectionClass(self::class);
        $parameters = $reflection->getConstructor()?->getParameters() ?? [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (!$parameter->isOptional()) {
                continue;
            }

            if (array_key_exists($name, $config) && $config[$name] === $parameter->getDefaultValue()) {
                unset($config[$name]);
            }
        }

        return $config;
    }
}
