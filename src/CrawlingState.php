<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use GuzzleHttp\Psr7\Uri;

/**
 * CrawlingState
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class CrawlingState
{
    public const SUCCESSFUL = 0;
    public const FAILED = 1;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var int
     */
    protected $state = self::SUCCESSFUL;

    /**
     * @var array
     */
    protected $data = [];

    public function __construct(Uri $uri, int $state = self::SUCCESSFUL, array $data = [])
    {
        $this->uri = $uri;
        $this->state = $state;
        $this->data = $data;
        $this->validateState();
    }

    public static function createSuccessful(Uri $uri, array $data = []): self
    {
        return new static($uri, self::SUCCESSFUL, $data);
    }

    public static function createFailed(Uri $uri, array $data = []): self
    {
        return new static($uri, self::FAILED, $data);
    }

    public function getUri(): Uri
    {
        return $this->uri;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return $this->is(self::SUCCESSFUL);
    }

    public function isFailed(): bool
    {
        return $this->is(self::FAILED);
    }

    public function is(int $state): bool
    {
        return $this->state === $state;
    }

    protected function validateState(): void
    {
        $supportedStates = [self::SUCCESSFUL, self::FAILED];
        if (!in_array($this->state, $supportedStates, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The given crawling state is not supported, use one of "%s" instead.',
                    implode('", "', $supportedStates)
                ),
                1604334815
            );
        }
    }
}
