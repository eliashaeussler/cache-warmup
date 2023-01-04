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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Normalizer;

use GuzzleHttp\Psr7;
use Psr\Http\Message;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer;

use function in_array;
use function is_string;
use function sprintf;

/**
 * UriDenormalizer.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UriDenormalizer implements Serializer\Normalizer\DenormalizerInterface
{
    private const SUPPORTED_TYPES = [
        Message\UriInterface::class,
        Psr7\Uri::class,
    ];

    /**
     * @template T of Message\UriInterface
     *
     * @param class-string<T>                      $type
     * @param array{deserialization_path?: string} $context
     *
     * @return T
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (!is_string($data)) {
            throw Serializer\Exception\NotNormalizableValueException::createForUnexpectedDataType(sprintf('The data is not a valid "%s" string representation.', $type), $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true);
        }

        if (Message\UriInterface::class === $type) {
            $type = Psr7\Uri::class;
        }

        return new $type($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        if (!is_string($data)) {
            return false;
        }

        return in_array($type, self::SUPPORTED_TYPES, true);
    }
}
