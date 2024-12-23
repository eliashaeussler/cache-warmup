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

use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Http;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message;
use Symfony\Component\OptionsResolver;

use function fclose;
use function fopen;
use function fread;
use function is_file;
use function is_readable;
use function is_resource;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function sha1;
use function simplexml_load_file;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

/**
 * SitemapXmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @phpstan-type ParserOptions array{
 *     request_headers: array<string, string>,
 *     request_options: array<string, mixed>,
 * }
 */
final class SitemapXmlParser implements ConfigurableParser
{
    private readonly OptionsResolver\OptionsResolver $optionsResolver;
    private readonly Node\SitemapNodeConverter $sitemapConverter;

    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    /**
     * @var ParserOptions
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        array $options = [],
        private readonly ClientInterface $client = new Client(),
    ) {
        $this->optionsResolver = $this->createOptionsResolver();
        $this->sitemapConverter = new Node\SitemapNodeConverter();

        $this->setOptions($options);
    }

    /**
     * @throws Exception\FileIsMissing
     * @throws Exception\FileIsNotReadable
     * @throws Exception\SitemapIsMalformed
     * @throws GuzzleException
     */
    public function parse(Sitemap\Sitemap $sitemap): Result\ParserResult
    {
        $filename = $this->fetchSitemapFile($sitemap);

        // Parse XML sitemap and collect possible errors
        $useInternalErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filename, null, LIBXML_NOCDATA);
        $errors = libxml_get_errors();

        // Reset internal libxml state
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        // Throw exception if XML parsing failed
        if ([] !== $errors || false === $xml) {
            throw new Exception\SitemapIsMalformed($sitemap, $errors);
        }

        $sitemaps = [];
        $urls = [];

        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $node) {
                /** @var array{loc?: string, lastmod?: string} $nodeArray */
                $nodeArray = (array) $node;
                $sitemaps[] = $this->sitemapConverter->convertSitemap($nodeArray, $sitemap);
            }
        }

        if (isset($xml->url)) {
            foreach ($xml->url as $node) {
                /** @var array{loc?: string, priority?: string, lastmod?: string, changefreq?: string} $nodeArray */
                $nodeArray = (array) $node;
                $urls[] = $this->sitemapConverter->convertUrl($nodeArray, $sitemap);
            }
        }

        return new Result\ParserResult($sitemaps, $urls);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        /* @phpstan-ignore assign.propertyType */
        $this->options = $this->optionsResolver->resolve($options);
    }

    /**
     * @throws Exception\FileIsMissing
     * @throws Exception\FileIsNotReadable
     * @throws GuzzleException
     */
    private function fetchSitemapFile(Sitemap\Sitemap $sitemap): string
    {
        $uri = $sitemap->getUri();

        // Fetch XML source
        if ($sitemap->isLocalFile()) {
            $filename = $sitemap->getLocalFilePath();
        } else {
            $filename = $this->downloadSitemap($uri);
        }

        // Check if file exists
        if (!is_file($filename) || !is_readable($filename)) {
            throw new Exception\FileIsMissing($filename);
        }

        $file = fopen($filename, 'rb');

        if (!is_resource($file)) {
            throw new Exception\FileIsNotReadable($filename);
        }

        // Use built-in gzip decoding if necessary
        if (0 === mb_strpos((string) fread($file, 10), "\x1f\x8b\x08")) {
            $filename = 'compress.zlib://'.$filename;
        }

        fclose($file);

        return $filename;
    }

    /**
     * @throws GuzzleException
     */
    private function downloadSitemap(Message\UriInterface $uri): string
    {
        $filename = $this->createTemporaryFilename((string) $uri);

        $request = Http\Message\RequestFactory::create('GET')
            ->withHeaders($this->options['request_headers'])
            ->withUserAgent(true)
            ->createRequest($uri)
        ;

        $requestOptions = $this->options['request_options'];
        $requestOptions[RequestOptions::SINK] = $filename;

        $this->client->send($request, $requestOptions);

        return $filename;
    }

    private function createTemporaryFilename(string $identifier): string
    {
        $salt = 0;

        do {
            $file = Helper\FilesystemHelper::joinPathSegments(
                sys_get_temp_dir(),
                sprintf('sitemap_%s_%d.xml', sha1($identifier), $salt++),
            );
        } while (is_file($file));

        return $this->temporaryFiles[] = $file;
    }

    private function createOptionsResolver(): OptionsResolver\OptionsResolver
    {
        $optionsResolver = new OptionsResolver\OptionsResolver();

        $optionsResolver->define('request_headers')
            ->allowedTypes('array')
            ->default([])
        ;

        $optionsResolver->define('request_options')
            ->allowedTypes('array')
            ->default([])
        ;

        return $optionsResolver;
    }

    public function __destruct()
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }
}
