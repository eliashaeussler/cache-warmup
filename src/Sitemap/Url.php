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

use DateTimeInterface;
use EliasHaeussler\CacheWarmup\Exception;
use GuzzleHttp\Psr7;
use Psr\Http\Message;

/**
 * Url.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class Url extends Psr7\Uri
{
    use UriValidationTrait;

    /**
     * @throws Exception\UrlIsEmpty
     * @throws Exception\UrlIsInvalid
     */
    public function __construct(
        protected string $uri,
        protected float $priority = 0.5,
        protected ?DateTimeInterface $lastModificationDate = null,
        protected ?ChangeFrequency $changeFrequency = null,
        protected ?Sitemap $origin = null,
    ) {
        parent::__construct($uri);
        $this->validateUri();
    }

    public function getUri(): Message\UriInterface
    {
        return $this;
    }

    public function getPriority(): float
    {
        return $this->priority;
    }

    public function getLastModificationDate(): ?DateTimeInterface
    {
        return $this->lastModificationDate;
    }

    public function getChangeFrequency(): ?ChangeFrequency
    {
        return $this->changeFrequency;
    }

    public function getOrigin(): ?Sitemap
    {
        return $this->origin;
    }

    public function getRootOrigin(): ?Sitemap
    {
        return $this->origin?->getRootOrigin() ?? $this->origin;
    }

    public function setOrigin(Sitemap $origin): self
    {
        $this->origin = $origin;

        return $this;
    }
}
