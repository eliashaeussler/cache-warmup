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

namespace EliasHaeussler\CacheWarmup\Result;

/**
 * CacheWarmupResult.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmupResult
{
    /**
     * @var list<CrawlingResult>
     */
    private array $successful = [];

    /**
     * @var list<CrawlingResult>
     */
    private array $failed = [];
    private bool $cancelled = false;

    public function addResult(CrawlingResult $result): self
    {
        if ($result->isSuccessful()) {
            $this->successful[] = $result;
        } elseif ($result->isFailed()) {
            $this->failed[] = $result;
        }

        return $this;
    }

    /**
     * @return list<CrawlingResult>
     */
    public function getSuccessful(): array
    {
        return $this->successful;
    }

    /**
     * @return list<CrawlingResult>
     */
    public function getFailed(): array
    {
        return $this->failed;
    }

    public function isSuccessful(): bool
    {
        return [] === $this->failed;
    }

    public function wasCancelled(): bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): void
    {
        $this->cancelled = $cancelled;
    }
}
