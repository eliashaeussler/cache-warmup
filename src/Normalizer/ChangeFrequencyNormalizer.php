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

namespace EliasHaeussler\CacheWarmup\Normalizer;

use EliasHaeussler\CacheWarmup\Sitemap;
use Symfony\Component\PropertyInfo;
use Symfony\Component\Serializer;
use ValueError;

use function is_string;

/**
 * ChangeFrequencyNormalizer.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ChangeFrequencyNormalizer implements Serializer\Normalizer\DenormalizerInterface
{
    /**
     * @param array{deserialization_path?: string} $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (!is_string($data)) {
            throw Serializer\Exception\NotNormalizableValueException::createForUnexpectedDataType('Change frequency data must be a string.', $data, [PropertyInfo\Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true);
        }

        try {
            return Sitemap\ChangeFrequency::fromCaseInsensitive($data);
        } catch (ValueError $error) {
            throw Serializer\Exception\NotNormalizableValueException::createForUnexpectedDataType($error->getMessage(), $data, [PropertyInfo\Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true, $error->getCode(), $error);
        }
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return Sitemap\ChangeFrequency::class === $type;
    }
}
