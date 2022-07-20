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

namespace EliasHaeussler\CacheWarmup\Result;

use EliasHaeussler\CacheWarmup\Sitemap;
use Psr\Http\Message;

use function in_array;

/**
 * ParserResult.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ParserResult
{
    /**
     * @var list<Sitemap>
     */
    private array $sitemaps = [];

    /**
     * @var list<Message\UriInterface>
     */
    private array $urls = [];

    public function add(Sitemap|Message\UriInterface $result): self
    {
        if ($result instanceof Sitemap) {
            return $this->addSitemap($result);
        }

        return $this->addUrl($result);
    }

    /**
     * @return list<Sitemap>
     */
    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    public function addSitemap(Sitemap $sitemap): self
    {
        if (!in_array($sitemap, $this->sitemaps)) {
            $this->sitemaps[] = $sitemap;
        }

        return $this;
    }

    /**
     * @return list<Message\UriInterface>
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    public function addUrl(Message\UriInterface $url): self
    {
        if (!in_array($url, $this->urls)) {
            $this->urls[] = $url;
        }

        return $this;
    }
}
