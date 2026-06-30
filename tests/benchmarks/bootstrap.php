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

use EliasHaeussler\TaskRunner;
use Symfony\Component\Console;
use Symfony\Component\Filesystem;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

final readonly class Benchmark
{
    private const BASE_URL = 'https://cache-warmup.ddev.site';

    private string $outputDir;
    private int $sitemapCount;
    private TaskRunner\TaskRunner $taskRunner;

    public function __construct(
        private int $totalUrls,
        private int $urlsPerFile = 45920,
        private Console\Output\ConsoleOutputInterface $output = new Console\Output\ConsoleOutput(),
        private Filesystem\Filesystem $filesystem = new Filesystem\Filesystem(),
    ) {
        $this->outputDir = dirname(__DIR__, 2).'/.build/web';
        $this->sitemapCount = (int) ceil($this->totalUrls / $this->urlsPerFile);
        $this->taskRunner = new TaskRunner\TaskRunner($this->output->section());
    }

    public function run(): void
    {
        $this->runOrFail('Wiping test instance', $this->wipeInstance(...));
        $this->runOrFail('Generating sitemap dummies', $this->generateSitemaps(...));
        $this->runOrFail('Adding main entrypoint', $this->copyEntrypoint(...));
        $this->taskRunner->run('Starting benchmark', static function (TaskRunner\RunnerContext $context) {
            $context->statusMessage = ' ';
        });
    }

    /**
     * @template T
     *
     * @param Closure(TaskRunner\RunnerContext): T $task
     */
    private function runOrFail(string $message, Closure $task): void
    {
        $result = $this->taskRunner->run($message, $task);

        if (TaskRunner\TaskResult::Failure === $result) {
            exit(1);
        }
    }

    private function wipeInstance(): void
    {
        $this->filesystem->remove($this->outputDir);
        $this->filesystem->mkdir($this->outputDir);
    }

    private function generateSitemaps(TaskRunner\RunnerContext $context): void
    {
        $output = $this->output->section();
        $progress = new Console\Helper\ProgressBar($output);

        $sitemapFiles = [];
        $index = 1;

        // Generate single XML sitemaps
        foreach ($progress->iterate(range(1, $this->sitemapCount)) as $fileNum) {
            $filename = sprintf('sitemap_%03d.xml', $fileNum);
            $filepath = sprintf('%s/%s', $this->outputDir, $filename);
            $urlsInFile = min($this->urlsPerFile, $this->totalUrls - ($fileNum - 1) * $this->urlsPerFile);

            $fh = fopen($filepath, 'wb');

            // Fail if file cannot be opened
            if (false === $fh) {
                $context->statusMessage = sprintf('<error>Cannot open %s for writing</error>', $filepath);
                $context->markAsFailed();

                return;
            }

            // Add XML prologue
            fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
            fwrite($fh, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL);

            // Add URLs
            for ($i = 0; $i < $urlsInFile; $i++, $index++) {
                fwrite($fh, sprintf('  <url><loc>%s/%s</loc></url>'.PHP_EOL, self::BASE_URL, $index));
            }

            // Close XML
            fwrite($fh, '</urlset>'.PHP_EOL);
            fclose($fh);

            $sitemapFiles[] = $filename;
        }

        // Write sitemap index
        $indexPath = sprintf('%s/sitemap.xml', $this->outputDir);
        $fh = fopen($indexPath, 'wb');

        if (false === $fh) {
            $context->statusMessage = sprintf('<error>Cannot open %s for writing.</error>', $indexPath);
            $context->markAsFailed();

            return;
        }

        // Add XML prologue
        fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
        fwrite($fh, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL);

        // Add sitemaps
        foreach ($sitemapFiles as $filename) {
            fwrite($fh, sprintf('  <sitemap><loc>%s/%s</loc></sitemap>'.PHP_EOL, self::BASE_URL, $filename));
        }

        // Close XML
        fwrite($fh, '</sitemapindex>'.PHP_EOL);
        fclose($fh);

        // Finalize
        $output->clear();
        $context->statusMessage = sprintf(
            '<info>Done (%d sitemaps, %d URLs)</info>',
            $this->sitemapCount,
            $index - 1,
        );
    }

    private function copyEntrypoint(): void
    {
        $this->filesystem->copy(__DIR__.'/index.php', $this->outputDir.'/index.php');
    }
}

(new Benchmark(
    (int) ($argv[1] ?? 1782804),
))->run();
