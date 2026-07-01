<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2026 Elias Häußler <elias@haeussler.dev>
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
use EliasHaeussler\CacheWarmup\Profiler;
use EliasHaeussler\CacheWarmup\Result;
use Stringable;
use Symfony\Component\Console;

use function array_map;
use function in_array;
use function is_array;
use function is_scalar;
use function json_encode;

/**
 * JsonFormatter.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @phpstan-type JsonArray array{
 *     parserResult?: array{
 *         success?: array{
 *             sitemaps: list<string>,
 *             urls: list<string>,
 *         },
 *         failure?: array{
 *             sitemaps: list<string>,
 *         },
 *         excluded?: array{
 *             sitemaps: list<string>,
 *             urls: list<string>,
 *         },
 *     },
 *     parserStatistics?: array{
 *         duration: float,
 *         memoryUsage: int,
 *         memoryPeak: int,
 *     },
 *     cacheWarmupResult?: array{
 *         success?: list<string>,
 *         failure?: list<string>,
 *         cancelled?: bool,
 *     },
 *     cacheWarmupStatistics?: array{
 *         duration: float,
 *         memoryUsage: int,
 *         memoryPeak: int,
 *     },
 *     messages?: array<value-of<MessageSeverity>, list<string>>,
 *     additionalStatistics?: array<string, array{
 *         duration: float,
 *         memoryUsage: int,
 *         memoryPeak: int,
 *     }>,
 * }
 */
final class JsonFormatter implements Formatter
{
    /**
     * @phpstan-var JsonArray
     */
    private array $json = [];

    /**
     * @var list<Profiler\MeasurementSpan>
     */
    private array $addedStatistics = [];

    public function __construct(
        private readonly Console\Style\SymfonyStyle $io,
    ) {}

    public function formatParserResult(
        Result\ParserResult $successful,
        Result\ParserResult $failed,
        Result\ParserResult $excluded,
        ?Profiler\MeasurementSpan $measurement = null,
    ): void {
        // Add successful result
        if ($this->io->isVeryVerbose()) {
            $this->addToJson('parserResult/success/sitemaps', $successful->getSitemaps());
            $this->addToJson('parserResult/success/urls', $successful->getUrls());
        }

        // Add failed result
        $this->addToJson('parserResult/failure/sitemaps', $failed->getSitemaps());

        // Add excluded result
        $this->addToJson('parserResult/excluded/sitemaps', $excluded->getSitemaps());
        $this->addToJson('parserResult/excluded/urls', $excluded->getUrls());

        // Add statistics
        if (null !== $measurement) {
            $this->addStatistic($measurement, 'parserStatistics');
        }
    }

    public function formatCacheWarmupResult(
        Result\CacheWarmupResult $result,
        ?Profiler\MeasurementSpan $measurement = null,
    ): void {
        $this->addToJson('cacheWarmupResult/success', $result->getSuccessful());
        $this->addToJson('cacheWarmupResult/failure', $result->getFailed());

        if ($result->wasCancelled()) {
            $this->addToJson('cacheWarmupResult/cancelled', true);
        }

        // Add statistics
        if (null !== $measurement) {
            $this->addStatistic($measurement, 'cacheWarmupStatistics');
        }
    }

    public function formatMeasuredScopes(array $measurements): void
    {
        foreach ($measurements as $measurement) {
            $this->addStatistic($measurement, 'additionalStatistics/'.$measurement->action);
        }
    }

    public function logMessage(string $message, MessageSeverity $severity = MessageSeverity::Info): void
    {
        if (!is_array($this->json['messages'] ?? null)) {
            $this->json['messages'] = [];
        }

        if (!is_array($this->json['messages'][$severity->value] ?? null)) {
            $this->json['messages'][$severity->value] = [];
        }

        $this->json['messages'][$severity->value][] = $message;
    }

    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * @phpstan-return JsonArray
     */
    public function getJson(): array
    {
        return $this->json;
    }

    public static function getType(): string
    {
        return 'json';
    }

    private function addStatistic(Profiler\MeasurementSpan $measurement, string $pathPrefix): void
    {
        if (in_array($measurement, $this->addedStatistics, true)) {
            return;
        }

        $this->addedStatistics[] = $measurement;

        $this->addToJson($pathPrefix.'/duration', $measurement->duration);
        $this->addToJson($pathPrefix.'/memoryUsage', $measurement->memoryUsage);
        $this->addToJson($pathPrefix.'/memoryPeak', $measurement->memoryPeak);
    }

    /**
     * @param string|float|int|bool|list<bool|float|int|resource|string|Stringable|null> $value
     */
    private function addToJson(string $path, string|float|int|bool|array $value): void
    {
        if (is_scalar($value) && '' !== $value) {
            Helper\ArrayHelper::setValueByPath($this->json, $path, $value);
        }
        if (is_array($value) && [] !== $value) {
            Helper\ArrayHelper::setValueByPath($this->json, $path, array_map(strval(...), $value));
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        // Early return if no JSON data was added
        if ([] === $this->json) {
            return;
        }

        // Early return if output is quiet
        if ($this->io->isQuiet()) {
            return;
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        // Pretty-print JSON on verbose output
        if ($this->io->isVerbose()) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->io->writeln(json_encode($this->json, $flags));
    }
}
