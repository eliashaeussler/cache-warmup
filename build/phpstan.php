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

use EliasHaeussler\PHPStanConfig;

$config = PHPStanConfig\Config\Config::create(dirname(__DIR__));
$config->createSet(PHPStanConfig\Set\SymfonySet::class)
    ->withConsoleApplicationLoader('tests/build/console-application.php')
;

return $config
    ->in(
        'bin/cache-warmup',
        'src',
        'tests',
    )
    ->withBaseline(__DIR__.'/phpstan-baseline.neon')
    ->withBleedingEdge()
    ->with(
        'vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-configuration.php',
        'vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-suppress-pure-errors.php',
    )
    ->useCacheDir('.build/cache/phpstan')
    ->maxLevel()
    ->toArray()
;
