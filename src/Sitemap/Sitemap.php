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

namespace EliasHaeussler\CacheWarmup\Sitemap;

use CuyZ\Valinor;
use DateTimeInterface;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Helper;
use GuzzleHttp\Psr7;
use Psr\Http\Message;
use Stringable;

use function is_array;
use function parse_str;
use function str_starts_with;
use function substr;
use function trim;
use function urldecode;
use function urlencode;

/**
 * Sitemap.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class Sitemap implements Stringable
{
    use UriValidationTrait;

    protected ?string $localFilePath = null;

    /**
     * @throws Exception\LocalFilePathIsMissingInUrl
     * @throws Exception\UrlIsEmpty
     * @throws Exception\UrlIsInvalid
     */
    public function __construct(
        protected Message\UriInterface $uri,
        protected ?DateTimeInterface $lastModificationDate = null,
        protected ?self $origin = null,
    ) {
        // BC layer: Convert file:// URIs to local files
        if ('file' === $uri->getScheme()) {
            $this->uri = self::convertLegacyFileUri((string) $this->uri);
        }

        $this->localFilePath = $this->extractLocalFilePath();

        $this->validateUri();
    }

    /**
     * @throws Exception\LocalFilePathIsMissingInUrl
     * @throws Exception\UrlIsEmpty
     * @throws Exception\UrlIsInvalid
     */
    #[Valinor\Mapper\Object\Constructor]
    public static function createFromString(string $sitemap): self
    {
        // Sitemap is a remote URL
        if (str_contains($sitemap, '://')) {
            return new self(new Psr7\Uri($sitemap));
        }

        // Sitemap is a local file
        $file = Helper\FilesystemHelper::resolveRelativePath($sitemap);
        $uri = self::convertLegacyFileUri('file://'.$file);

        return new self($uri);
    }

    public function getUri(): Message\UriInterface
    {
        return $this->uri;
    }

    /**
     * @phpstan-assert-if-true !null $this->getLocalFilePath()
     */
    public function isLocalFile(): bool
    {
        return null !== $this->localFilePath;
    }

    public function getLocalFilePath(): ?string
    {
        return $this->localFilePath;
    }

    public function getLastModificationDate(): ?DateTimeInterface
    {
        return $this->lastModificationDate;
    }

    public function getOrigin(): ?self
    {
        return $this->origin;
    }

    public function getRootOrigin(): ?self
    {
        return $this->origin?->getRootOrigin() ?? $this->origin;
    }

    public function setOrigin(self $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->uri;
    }

    /**
     * @throws Exception\LocalFilePathIsMissingInUrl
     * @throws Exception\UrlIsInvalid
     */
    protected function extractLocalFilePath(): ?string
    {
        $uri = (string) $this->uri;

        // Early return if URI is not supported
        if ('local' !== $this->uri->getScheme() || 'file' !== $this->uri->getHost()) {
            return null;
        }

        parse_str($this->uri->getQuery(), $queryParams);

        $filePath = $queryParams['path'] ?? null;

        // Treat local file URIs with associative "path" query parameter as invalid
        if (is_array($filePath)) {
            throw new Exception\UrlIsInvalid($uri);
        }

        // Fail if path is not properly defined
        if (null === $filePath) {
            throw new Exception\LocalFilePathIsMissingInUrl($uri);
        }

        // Fail if path is empty
        if ('' === trim($filePath)) {
            throw new Exception\LocalFilePathIsMissingInUrl($uri);
        }

        return urldecode($filePath);
    }

    protected static function convertLegacyFileUri(string $uri): Message\UriInterface
    {
        if (!str_starts_with($uri, 'file://')) {
            return new Psr7\Uri($uri);
        }

        $file = substr($uri, 7);

        return new Psr7\Uri('local://file?path='.urlencode($file));
    }
}
