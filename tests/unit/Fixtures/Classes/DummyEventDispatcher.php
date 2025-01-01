<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Fixtures\Classes;

use Symfony\Component\EventDispatcher;

/**
 * DummyEventDispatcher.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DummyEventDispatcher extends EventDispatcher\EventDispatcher
{
    /**
     * @var array<string, non-negative-int>
     */
    private array $dispatchedEvents = [];

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->track($eventName ?? $event::class);

        return parent::dispatch($event, $eventName);
    }

    private function track(string $eventName): void
    {
        if (!isset($this->dispatchedEvents[$eventName])) {
            $this->dispatchedEvents[$eventName] = 0;
        }

        ++$this->dispatchedEvents[$eventName];
    }

    public function wasDispatched(string $eventName): bool
    {
        return $this->numberOfDispatchedEventsFor($eventName) > 0;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfDispatchedEventsFor(string $eventName): int
    {
        return $this->dispatchedEvents[$eventName] ?? 0;
    }
}
