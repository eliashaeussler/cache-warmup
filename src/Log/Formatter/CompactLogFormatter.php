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

namespace EliasHaeussler\CacheWarmup\Log\Formatter;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Stringable;

use function get_debug_type;
use function is_scalar;
use function json_encode;
use function preg_replace_callback;
use function sprintf;
use function strtoupper;

/**
 * CompactLogFormatter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CompactLogFormatter implements LogFormatter
{
    private const TEMPLATE = '[%s] %s: %s %s';

    public function format(string $level, Stringable|string $message, array $context = []): string
    {
        $date = new DateTimeImmutable();
        $formattedMessage = $this->formatMessage($message, $context);
        $context = $this->formatContext($context);

        return sprintf(
            self::TEMPLATE,
            $date->format(DateTimeInterface::ATOM),
            strtoupper($level),
            $formattedMessage,
            json_encode($context),
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function formatMessage(Stringable|string $message, array &$context = []): string
    {
        $formattedMessage = preg_replace_callback(
            '/\{([^\s}]+)}/',
            function (array $matches) use (&$context) {
                return $this->replacePlaceholder($matches[0], $matches[1], $context);
            },
            (string) $message,
        );

        // Return original message on failures
        if (null === $formattedMessage) {
            return (string) $message;
        }

        return $formattedMessage;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function replacePlaceholder(string $placeholder, string $placeholderKey, array &$context): string
    {
        // Keep placeholder if no replacement is available
        if (!isset($context[$placeholderKey])) {
            return $placeholder;
        }

        // Replace placeholder value
        $value = $context[$placeholderKey];

        if (!is_scalar($value) && !($value instanceof Stringable)) {
            return $placeholder;
        }

        // Remove processed placeholder from context
        unset($context[$placeholderKey]);

        return (string) $value;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function formatContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value instanceof Stringable || $value instanceof JsonSerializable) {
                continue;
            }

            $context[$key] = get_debug_type($value);
        }

        return $context;
    }
}
