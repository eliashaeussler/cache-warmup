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
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use Netlogix\XmlProcessor;
use Psr\Http\Message;

use function array_map;
use function fclose;
use function fopen;
use function fread;
use function is_file;
use function is_readable;
use function is_resource;
use function restore_error_handler;
use function set_error_handler;
use function sha1;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

/**
 * XmlParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlParser
{
    private readonly Node\SitemapNodeProcessor $sitemapProcessor;
    private readonly Node\SitemapNodeProcessor $urlProcessor;
    private readonly XmlProcessor\XmlProcessor $xmlProcessor;
    private readonly Node\SitemapNodeConverter $sitemapConverter;

    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    public function __construct(
        private readonly ClientInterface $client = new Client(),
    ) {
        $this->sitemapProcessor = new Node\SitemapNodeProcessor(
            Node\SitemapNodePath::Sitemap,
            [
                Node\SitemapNode::LastModificationDate,
                Node\SitemapNode::Location,
            ],
        );
        $this->urlProcessor = new Node\SitemapNodeProcessor(
            Node\SitemapNodePath::Url,
            [
                Node\SitemapNode::ChangeFrequency,
                Node\SitemapNode::LastModificationDate,
                Node\SitemapNode::Location,
                Node\SitemapNode::Priority,
            ],
        );
        $this->xmlProcessor = new XmlProcessor\XmlProcessor([
            $this->sitemapProcessor,
            $this->urlProcessor,
        ]);
        $this->sitemapConverter = new Node\SitemapNodeConverter();
    }

    /**
     * @throws Exception\FileIsMissing
     * @throws Exception\FileIsNotReadable
     * @throws Exception\SitemapCannotBeRead
     * @throws Exception\SitemapIsMalformed
     * @throws GuzzleException
     */
    public function parse(Sitemap\Sitemap $sitemap): Result\ParserResult
    {
        $filename = $this->fetchSitemapFile($sitemap);

        set_error_handler(
            static fn () => throw new Exception\SitemapCannotBeRead($sitemap),
        );

        $this->sitemapProcessor->reset();
        $this->urlProcessor->reset();

        try {
            $this->xmlProcessor->processFile($filename);
        } catch (Exception\XmlNodeIsEmpty $exception) {
            throw new Exception\SitemapIsMalformed($sitemap, $exception);
        } finally {
            restore_error_handler();
        }

        $sitemaps = $this->sitemapProcessor->getProcessedNodes();
        $urls = $this->urlProcessor->getProcessedNodes();

        return new Result\ParserResult(
            array_map(fn (array $node) => $this->sitemapConverter->convertSitemap($node, $sitemap), $sitemaps),
            array_map(fn (array $node) => $this->sitemapConverter->convertUrl($node, $sitemap), $urls),
        );
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

        $this->client->send(
            new Psr7\Request('GET', $uri),
            [
                RequestOptions::SINK => $filename,
            ],
        );

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

    public function __destruct()
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }
}
