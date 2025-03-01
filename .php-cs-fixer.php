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

use EliasHaeussler\PhpCsFixerConfig;
use Symfony\Component\Finder;

$header = PhpCsFixerConfig\Rules\Header::create(
    'eliashaeussler/cache-warmup',
    PhpCsFixerConfig\Package\Type::ComposerPackage,
    PhpCsFixerConfig\Package\Author::create('Elias Häußler', 'elias@haeussler.dev'),
    PhpCsFixerConfig\Package\CopyrightRange::from(2020),
    PhpCsFixerConfig\Package\License::GPL3OrLater,
);

// @todo Re-enable once https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/8462 is resolved
$disableFalsePositiveInPHPUnit = PhpCsFixerConfig\Rules\RuleSet::fromArray([
    'php_unit_method_casing' => false,
]);

return PhpCsFixerConfig\Config::create()
    ->withRule($header)
    ->withRule($disableFalsePositiveInPHPUnit)
    ->withFinder(
        static fn (Finder\Finder $finder) => $finder->in(__DIR__)->name(['cache-warmup', '*.php']),
    )
;
