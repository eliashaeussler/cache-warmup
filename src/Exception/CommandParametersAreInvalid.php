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

namespace EliasHaeussler\CacheWarmup\Exception;

use CuyZ\Valinor;

use function implode;
use function sprintf;

/**
 * CommandParametersAreInvalid.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CommandParametersAreInvalid extends Exception
{
    /**
     * @param array<string, string> $nameMapping
     */
    public function __construct(Valinor\Mapper\MappingError $error, array $nameMapping = [])
    {
        $errorMessages = $this->formatMappingError($error, $nameMapping);

        parent::__construct(
            sprintf(
                'Some command parameters are invalid:%s%s',
                PHP_EOL,
                implode(PHP_EOL, $errorMessages),
            ),
            1708712872,
        );
    }
}
