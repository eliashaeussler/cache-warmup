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

use EliasHaeussler\PHPStanConfig;

$symfonySet = PHPStanConfig\Set\SymfonySet::create()
    ->withConsoleApplicationLoader('tests/build/console-application.php')
    ->withContainerXmlPath('.build/container.xml')
;

return PHPStanConfig\Config\Config::create(__DIR__)
    ->in(
        'bin/cache-warmup',
        'src',
        'tests',
    )
    ->withBaseline()
    ->withBleedingEdge()
    ->with('vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-configuration.php')
    ->maxLevel()
    ->withSets($symfonySet)
    ->toArray()
;
