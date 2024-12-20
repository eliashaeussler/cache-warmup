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

namespace EliasHaeussler\CacheWarmup\Config\Component;

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup\Exception;

use function array_filter;
use function is_array;

/**
 * OptionsParser.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class OptionsParser
{
    private Valinor\Mapper\TreeMapper $mapper;

    public function __construct()
    {
        $this->mapper = $this->createMapper();
    }

    /**
     * @param string|array<mixed>|null $options
     *
     * @return array<string, mixed>
     *
     * @throws Exception\OptionsAreInvalid
     * @throws Exception\OptionsAreMalformed
     */
    public function parse(string|array|null $options): array
    {
        if (null === $options) {
            return [];
        }

        try {
            if (is_array($options)) {
                $source = Valinor\Mapper\Source\Source::array($options);
            } else {
                $source = Valinor\Mapper\Source\Source::json($options);
            }

            $result = $this->mapper->map('array<string, mixed>', $source);
        } catch (Valinor\Mapper\MappingError $error) {
            throw new Exception\OptionsAreInvalid($error);
        } catch (Valinor\Mapper\Source\Exception\InvalidSource $exception) {
            throw new Exception\OptionsAreMalformed($exception->source(), $exception);
        }

        // Handle non-associative-array options
        if ($result !== array_filter($result, 'is_string', ARRAY_FILTER_USE_KEY)) {
            throw new Exception\OptionsAreInvalid();
        }

        return $result;
    }

    private function createMapper(): Valinor\Mapper\TreeMapper
    {
        return (new Valinor\MapperBuilder())
            ->allowPermissiveTypes()
            ->mapper()
        ;
    }
}
