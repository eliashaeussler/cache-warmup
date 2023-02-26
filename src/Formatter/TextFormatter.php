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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Formatter;

use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Time;
use Symfony\Component\Console;

use function array_map;
use function method_exists;
use function sprintf;

/**
 * TextFormatter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class TextFormatter implements Formatter
{
    public function __construct(
        private readonly Console\Style\SymfonyStyle $io,
    ) {
    }

    public function formatParserResult(
        Result\ParserResult $successful,
        Result\ParserResult $failed,
        Result\ParserResult $excluded,
        Time\Duration $duration = null,
    ): void {
        if ($this->io->isVeryVerbose()) {
            // Print parsed sitemaps
            $this->io->section('The following sitemaps were processed:');
            $this->io->listing(array_map('strval', $successful->getSitemaps()));

            // Print parsed URLs
            $this->io->section('The following URLs will be crawled:');
            $this->io->listing($successful->getUrls());
        }

        // Print failed sitemaps
        if ([] !== ($failedSitemaps = $failed->getSitemaps())) {
            $this->io->section('The following sitemaps could not be parsed:');
            $this->io->listing(array_map('strval', $failedSitemaps));
        }

        // Print excluded sitemaps
        if ([] !== ($excludedSitemaps = $excluded->getSitemaps())) {
            $this->io->section('The following sitemaps were excluded by a pattern:');
            $this->io->listing(array_map('strval', $excludedSitemaps));
        }
        if ([] !== ($excludedUrls = $excluded->getUrls())) {
            $this->io->section('The following URLs were excluded by a pattern:');
            $this->io->listing(array_map('strval', $excludedUrls));
        }

        // Print duration
        if ($this->io->isVeryVerbose() && null !== $duration) {
            $this->io->writeln(sprintf('Parsing finished in %s', $duration->format()));
        }
    }

    public function formatCacheWarmupResult(
        Result\CacheWarmupResult $result,
        Time\Duration $duration = null,
    ): void {
        $successfulUrls = $result->getSuccessful();
        $failedUrls = $result->getFailed();

        // Print crawler statistics
        if ($this->io->isVeryVerbose()) {
            $this->io->newLine();

            if ([] !== $successfulUrls) {
                $this->io->section('The following URLs were successfully crawled:');
                $this->io->listing(array_map('strval', $successfulUrls));
            }
        }
        if ($this->io->isVerbose() && [] !== $failedUrls) {
            $this->io->section('The following URLs failed during crawling:');
            $this->io->listing(array_map('strval', $failedUrls));
        }

        // Print crawler results
        if ([] !== $successfulUrls) {
            $countSuccessfulUrls = count($successfulUrls);
            $this->io->success(
                sprintf(
                    'Successfully warmed up caches for %d URL%s.',
                    $countSuccessfulUrls,
                    1 === $countSuccessfulUrls ? '' : 's',
                ),
            );
        }

        if ([] !== $failedUrls) {
            $countFailedUrls = count($failedUrls);
            $this->io->error(
                sprintf(
                    'Failed to warm up caches for %d URL%s.',
                    $countFailedUrls,
                    1 === $countFailedUrls ? '' : 's',
                ),
            );
        }

        // Print duration
        if (null !== $duration) {
            $this->io->writeln(sprintf('Crawling finished in %s', $duration->format()));
            $this->io->newLine();
        }
    }

    public function logMessage(string $message, MessageSeverity $severity = MessageSeverity::Info): void
    {
        $methodName = $severity->value;

        if (method_exists($this->io, $methodName)) {
            /* @phpstan-ignore-next-line */
            $this->io->{$methodName}($message);
        }
    }

    public function isVerbose(): bool
    {
        return true;
    }

    public static function getType(): string
    {
        return 'text';
    }
}
