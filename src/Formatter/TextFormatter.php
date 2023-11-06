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

use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Time;
use Symfony\Component\Console;

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
        Helper\ConsoleHelper::registerAdditionalConsoleOutputStyles($this->io->getFormatter());
    }

    public function formatParserResult(
        Result\ParserResult $successful,
        Result\ParserResult $failed,
        Result\ParserResult $excluded,
        Time\Duration $duration = null,
    ): void {
        $sitemaps = [];
        $urlsShown = false;

        // Add successful sitemaps
        if ($this->io->isVerbose()) {
            foreach ($successful->getSitemaps() as $successfulSitemap) {
                $sitemaps[] = sprintf('<success> DONE </> <href=%1$s>%1$s</>', (string) $successfulSitemap->getUri());
            }
        }

        // Add excluded sitemaps
        foreach ($excluded->getSitemaps() as $excludedSitemap) {
            $sitemaps[] = sprintf('<skipped> SKIP </> <href=%1$s>%1$s</>', (string) $excludedSitemap->getUri());
        }

        // Add failed sitemaps
        foreach ($failed->getSitemaps() as $failedSitemap) {
            $sitemaps[] = sprintf('<failure> FAIL </> <href=%1$s>%1$s</>', (string) $failedSitemap->getUri());
        }

        // Print processed sitemaps
        if ([] !== $sitemaps) {
            $this->io->section('Parsed sitemaps');
            $this->io->writeln($sitemaps);
        }

        // Print parsed URLs
        if ($this->io->isDebug() && [] !== $successful->getUrls()) {
            $urlsShown = true;

            $this->io->section('Parsed URLs');

            foreach ($successful->getUrls() as $successfulUrl) {
                $this->io->writeln(sprintf('<success> DONE </> <href=%1$s>%1$s</>', (string) $successfulUrl));
            }
        }

        // Print excluded URLs
        if ([] !== $excluded->getUrls()) {
            $urlsShown = true;

            $this->io->section('Excluded URLs');

            foreach ($excluded->getUrls() as $excludedUrl) {
                $this->io->writeln(sprintf('<skipped> SKIP </> <href=%1$s>%1$s</>', (string) $excludedUrl));
            }
        }

        // Print duration
        if ($this->io->isDebug() && null !== $duration) {
            $this->io->newLine();
            $this->io->writeln(sprintf('Parsing finished in %s', $duration->format()));
        }

        if ([] !== $sitemaps || $urlsShown) {
            $this->io->newLine();
        }
    }

    public function formatCacheWarmupResult(
        Result\CacheWarmupResult $result,
        Time\Duration $duration = null,
    ): void {
        $successfulUrls = $result->getSuccessful();
        $failedUrls = $result->getFailed();
        $urls = [];

        // Add successful URLs
        if ($this->io->isDebug()) {
            foreach ($successfulUrls as $successfulUrl) {
                $urls[] = sprintf('<success> DONE </> <href=%1$s>%1$s</>', (string) $successfulUrl);
            }
        }

        // Add failed URLs
        if ($this->io->isVerbose()) {
            foreach ($failedUrls as $failedUrl) {
                $urls[] = sprintf('<failure> FAIL </> <href=%1$s>%1$s</>', (string) $failedUrl);
            }
        }

        // Prints URLs
        if ([] !== $urls) {
            $this->io->section('Crawled URLs');
            $this->io->writeln($urls);
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
        if ($result->wasCancelled()) {
            $this->io->warning('Cache warmup was cancelled due to a crawling failure.');
        }

        // Print duration
        if (null !== $duration) {
            $this->io->writeln(
                sprintf(
                    'Crawling %s %s',
                    $result->wasCancelled() ? 'cancelled after' : 'finished in',
                    $duration->format(),
                ),
            );
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
