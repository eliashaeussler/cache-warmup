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

namespace EliasHaeussler\CacheWarmup\Mapper\Source;

use EliasHaeussler\CacheWarmup\Exception;
use IteratorAggregate;
use Mtownsend\XmlToArray;
use Throwable;
use Traversable;

use function array_is_list;
use function is_array;
use function restore_error_handler;
use function set_error_handler;

/**
 * XmlSource.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @implements IteratorAggregate<string, mixed>
 */
final class XmlSource implements IteratorAggregate
{
    /**
     * @param array<string, mixed> $source
     */
    private function __construct(
        private array $source,
    ) {}

    /**
     * @throws Exception\XmlIsMalformed
     */
    public static function fromXml(string $xml): self
    {
        set_error_handler(static fn (int $code, string $message) => self::handleParseError($xml, $message));

        try {
            $source = XmlToArray\XmlToArray::convert($xml);
        } catch (Throwable $exception) {
            self::handleParseError($xml, $exception->getMessage());
        } finally {
            restore_error_handler();
        }

        return new self($source);
    }

    public function asCollection(string $node): self
    {
        $clone = clone $this;

        if (isset($clone->source[$node]) && is_array($clone->source[$node]) && !array_is_list($clone->source[$node])) {
            $clone->source[$node] = [$clone->source[$node]];
        }

        return $clone;
    }

    public function getIterator(): Traversable
    {
        yield from $this->source;
    }

    /**
     * @throws Exception\XmlIsMalformed
     */
    private static function handleParseError(string $xml, string $message): never
    {
        throw new Exception\XmlIsMalformed($xml, $message);
    }
}
