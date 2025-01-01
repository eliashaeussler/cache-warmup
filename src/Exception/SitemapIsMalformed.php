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

use EliasHaeussler\CacheWarmup\Sitemap;
use LibXMLError;
use Throwable;

use function array_map;
use function implode;
use function sprintf;

/**
 * SitemapIsMalformed.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SitemapIsMalformed extends Exception
{
    /**
     * @param array<LibXMLError> $errors
     */
    public function __construct(Sitemap\Sitemap $sitemap, array $errors = [], ?Throwable $previous = null)
    {
        if ([] !== $errors) {
            $suffix = sprintf(
                ':%s%s',
                PHP_EOL,
                implode(PHP_EOL, array_map($this->decorateError(...), $errors)),
            );
        } elseif (null !== $previous) {
            $suffix = sprintf(':%s  * %s', PHP_EOL, $previous->getMessage());
        } else {
            $suffix = '.';
        }

        parent::__construct(
            sprintf(
                'Sitemap "%s" is malformed and cannot be parsed%s',
                $sitemap->isLocalFile() ? $sitemap->getLocalFilePath() : (string) $sitemap,
                $suffix,
            ),
            1733161983,
            $previous,
        );
    }

    private function decorateError(LibXMLError $error): string
    {
        return sprintf('  * Line %d: %s', $error->line, $error->message);
    }
}
