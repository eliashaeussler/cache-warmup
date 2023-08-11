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

namespace EliasHaeussler\CacheWarmup\Tests\Log;

use EliasHaeussler\CacheWarmup\Log;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

/**
 * DummyLogger.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class DummyLogger extends AbstractLogger
{
    /**
     * @var array<value-of<Log\LogLevel>, list<array{message: string|Stringable, context: array<string, mixed>}>>
     */
    public array $log = [];

    public function __construct()
    {
        foreach (Log\LogLevel::cases() as $logLevel) {
            $this->log[$logLevel->value] = [];
        }
    }

    /**
     * @phpstan-param LogLevel::* $level
     *
     * @param array<string, mixed> $context
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $logLevel = Log\LogLevel::fromPsrLogLevel($level);

        $this->log[$logLevel->value][] = [
            'message' => $message,
            'context' => $context,
        ];
    }
}
