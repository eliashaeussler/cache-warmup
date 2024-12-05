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

namespace EliasHaeussler\CacheWarmup\Xml\Node;

use DateTimeImmutable;
use DateTimeInterface;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Psr7;

/**
 * SitemapNodeConverter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class SitemapNodeConverter
{
    private const DATE_FORMATS = [
        DateTimeInterface::W3C,
        'Y-m-d\TH:i:s.v\Z',
        '!Y-m-d',
    ];

    /**
     * @param array{loc?: string, lastmod?: string} $node
     *
     * @throws Exception\SitemapIsMalformed
     */
    public function convertSitemap(array $node, Sitemap\Sitemap $origin): Sitemap\Sitemap
    {
        if (!isset($node[SitemapNode::Location->value])) {
            throw new Exception\SitemapIsMalformed($origin);
        }

        $uri = new Psr7\Uri($node[SitemapNode::Location->value]);
        $lastModificationDate = null;

        if (isset($node[SitemapNode::LastModificationDate->value])) {
            $lastModificationDate = $this->parseLastModificationDate($node[SitemapNode::LastModificationDate->value]);
        }

        try {
            return new Sitemap\Sitemap($uri, $lastModificationDate, $origin);
        } catch (Exception\LocalFilePathIsMissingInUrl|Exception\UrlIsEmpty|Exception\UrlIsInvalid $exception) {
            throw new Exception\SitemapIsMalformed($origin, $exception);
        }
    }

    /**
     * @param array{loc?: string, priority?: string, lastmod?: string, changefreq?: string} $node
     *
     * @throws Exception\SitemapIsMalformed
     */
    public function convertUrl(array $node, Sitemap\Sitemap $origin): Sitemap\Url
    {
        if (!isset($node[SitemapNode::Location->value])) {
            throw new Exception\SitemapIsMalformed($origin);
        }

        $uri = $node[SitemapNode::Location->value];
        $priority = (float) ($node[SitemapNode::Priority->value] ?? 0.5);
        $lastModificationDate = null;
        $changeFrequency = null;

        if (isset($node[SitemapNode::LastModificationDate->value])) {
            $lastModificationDate = $this->parseLastModificationDate($node[SitemapNode::LastModificationDate->value]);
        }
        if (isset($node[SitemapNode::ChangeFrequency->value])) {
            $changeFrequency = Sitemap\ChangeFrequency::fromCaseInsensitive($node[SitemapNode::ChangeFrequency->value]);
        }

        try {
            return new Sitemap\Url($uri, $priority, $lastModificationDate, $changeFrequency, $origin);
        } catch (Exception\UrlIsEmpty|Exception\UrlIsInvalid $exception) {
            throw new Exception\SitemapIsMalformed($origin, $exception);
        }
    }

    private function parseLastModificationDate(string $datetime): ?DateTimeImmutable
    {
        foreach (self::DATE_FORMATS as $dateFormat) {
            try {
                $lastModificationDate = DateTimeImmutable::createFromFormat($dateFormat, $datetime);
            } catch (\Exception) {
                // Try next format
                continue;
            }

            if (false !== $lastModificationDate) {
                return $lastModificationDate;
            }
        }

        return null;
    }
}
