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

namespace EliasHaeussler\CacheWarmup\Command;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Formatter;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use Symfony\Component\Console;

use function count;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function sprintf;

/**
 * CacheWarmupCommand.
 *
 * @author Elias Häußler <elias@heussler.dev>
 * @license GPL-3.0-or-later
 */
final class CacheWarmupCommand extends Console\Command\Command
{
    private const SUCCESSFUL = 0;
    private const FAILED = 1;

    private Console\Style\SymfonyStyle $io;
    private Formatter\Formatter $formatter;
    private Crawler\CrawlerFactory $crawlerFactory;

    public function __construct(
        private readonly ClientInterface $client = new Client(),
    ) {
        parent::__construct('cache-warmup');
    }

    protected function configure(): void
    {
        $crawlerInterface = Crawler\CrawlerInterface::class;
        $configurableCrawlerInterface = Crawler\ConfigurableCrawlerInterface::class;
        $textFormatter = Formatter\TextFormatter::getType();
        $jsonFormatter = Formatter\JsonFormatter::getType();

        $this->setDescription('Warms up caches of URLs provided by a given set of XML sitemaps.');
        $this->setHelp(<<<HELP
This command can be used to warm up website caches.
It requires a set of XML sitemaps offering several URLs which will be crawled.

<info>Sitemaps</info>
<info>========</info>
The list of sitemaps to be crawled can be defined as command argument:

   <comment>%command.full_name% https://www.example.com/sitemap.xml</comment>

You are free to crawl as many sitemaps as you want.
Alternatively, sitemaps can be specified from user input when application is in interactive mode.

<info>Custom URLs</info>
<info>===========</info>
In addition or as an alternative to sitemaps, it's also possible to provide a given URL set using the <comment>--urls</comment> option:

   <comment>%command.full_name% -u https://www.example.com/foo -u https://www.example.com/baz</comment>

<info>Progress bar</info>
<info>============</info>
You can track the cache warmup progress by using the <comment>--progress</comment> option:

   <comment>%command.full_name% --progress</comment>

This shows a compact progress bar, including current warmup failures.
For a more verbose output, add the <comment>--verbose</comment> option:

   <comment>%command.full_name% --progress --verbose</comment>

<info>URL limit</info>
<info>=========</info>
The number of URLs to be crawled can be limited using the <comment>--limit</comment> option:

   <comment>%command.full_name% --limit 50</comment>

<info>Crawler</info>
<info>=======</info>
By default, cache warmup will be done using concurrent HEAD requests.
This behavior can be overridden in case a special crawler is defined using the <comment>--crawler</comment> option:

   <comment>%command.full_name% --crawler "Vendor\Crawler\MyCrawler"</comment>

It's up to you to ensure the given crawler class is available and fully loaded.
This can best be achieved by registering the class with Composer autoloader.
Also make sure the crawler implements the <comment>{$crawlerInterface}</comment> interface.

<info>Crawler options</info>
<info>===============</info>
For crawlers implementing the <comment>{$configurableCrawlerInterface}</comment> interface,
it is possible to pass a JSON-encoded array of crawler options by using the <comment>--crawler-options</comment> option:

   <comment>%command.full_name% --crawler-options '{"concurrency": 3}'</comment>

<info>Allow failures</info>
<info>==============</info>
If a sitemap cannot be parsed or a URL fails to be crawled, this command normally exits
with a non-zero exit code. This is not always the desired behavior. Therefore, you can change
this behavior by using the <comment>--allow-failures</comment> option:

   <comment>%command.full_name% --allow-failures</comment>

<info>Format output</info>
<info>=============</info>
By default, all user-oriented output is printed as plain text to the console.
However, you can use other formatters by using the <comment>--format</comment> option:

   <comment>%command.full_name% --format json</comment>

Currently, the following formatters are available:

   * <comment>{$textFormatter}</comment> (default)
   * <comment>{$jsonFormatter}</comment>

HELP);

        $this->addArgument(
            'sitemaps',
            Console\Input\InputArgument::OPTIONAL | Console\Input\InputArgument::IS_ARRAY,
            'URLs of XML sitemaps to be used for cache warming',
        );
        $this->addOption(
            'urls',
            'u',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Custom additional URLs to be used for cache warming',
        );
        $this->addOption(
            'limit',
            'l',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Limit the number of URLs to be processed',
            0,
        );
        $this->addOption(
            'progress',
            'p',
            Console\Input\InputOption::VALUE_NONE,
            'Show progress bar during cache warmup',
        );
        $this->addOption(
            'crawler',
            'c',
            Console\Input\InputOption::VALUE_REQUIRED,
            'FQCN of the crawler to be used for cache warming',
        );
        $this->addOption(
            'crawler-options',
            'o',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Additional config for configurable crawlers',
        );
        $this->addOption(
            'allow-failures',
            null,
            Console\Input\InputOption::VALUE_NONE,
            'Allow failures during URL crawling and exit with zero',
        );
        $this->addOption(
            'format',
            'f',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Formatter used to print the cache warmup result',
            Formatter\TextFormatter::getType(),
        );
    }

    protected function initialize(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): void
    {
        $this->io = new Console\Style\SymfonyStyle($input, $output);
        $this->formatter = (new Formatter\FormatterFactory($this->io))->get($input->getOption('format'));
        $this->crawlerFactory = new Crawler\CrawlerFactory($output);
    }

    protected function interact(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): void
    {
        // Early return if sitemaps or URLs are already specified
        if ([] !== $input->getArgument('sitemaps') || [] !== $input->getOption('urls')) {
            return;
        }

        // Get sitemaps from interactive user input
        $sitemaps = [];
        $helper = $this->getHelper('question');
        do {
            $question = new Console\Question\Question('Please enter the URL of a XML sitemap: ');
            $question->setValidator($this->validateSitemap(...));
            $sitemap = $helper->ask($input, $output, $question);
            if ($sitemap instanceof Sitemap\Sitemap) {
                $sitemaps[] = $sitemap;
                $output->writeln(sprintf('<info>Sitemap added: %s</info>', $sitemap));
            }
        } while ($sitemap instanceof Sitemap\Sitemap);

        // Throw exception if no sitemaps were added
        if ([] === $sitemaps) {
            throw new Console\Exception\RuntimeException('You must enter at least one sitemap URL.', 1604258903);
        }

        $input->setArgument('sitemaps', $sitemaps);
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $sitemaps = $input->getArgument('sitemaps');
        $urls = $input->getOption('urls');

        // Throw exception if neither sitemaps nor URLs are defined
        if ([] === $sitemaps && [] === $urls) {
            throw new Console\Exception\RuntimeException('Neither sitemaps nor URLs are defined.', 1604261236);
        }

        // Initialize components
        $crawler = $this->initializeCrawler($input);
        $cacheWarmer = $this->initializeCacheWarmer($input, $crawler);

        // Print formatted parser result
        $this->formatter->formatParserResult(
            new Result\ParserResult($cacheWarmer->getSitemaps(), $cacheWarmer->getUrls()),
            new Result\ParserResult($cacheWarmer->getFailedSitemaps()),
        );

        // Start crawling
        $result = $this->runCacheWarmup(
            $cacheWarmer,
            $crawler instanceof Crawler\VerboseCrawlerInterface,
        );

        // Print formatted cache warmup result
        $this->formatter->formatCacheWarmupResult($result);

        // Early return if failures are allowed
        if ($input->getOption('allow-failures')) {
            return self::SUCCESSFUL;
        }

        // Early return if parsing or crawling failed
        if ([] !== $cacheWarmer->getFailedSitemaps() || !$result->isSuccessful()) {
            return self::FAILED;
        }

        return self::SUCCESSFUL;
    }

    private function runCacheWarmup(CacheWarmer $cacheWarmer, bool $isVerboseCrawler): Result\CacheWarmupResult
    {
        $urlCount = count($cacheWarmer->getUrls());

        if ($this->formatter->isVerbose()) {
            $this->io->write(sprintf('Crawling URL%s... ', 1 === $urlCount ? '' : 's'), $isVerboseCrawler);
        }

        $result = $cacheWarmer->run();

        if ($this->formatter->isVerbose() && !$isVerboseCrawler) {
            $this->io->writeln('<info>Done</info>');
        }

        return $result;
    }

    private function initializeCacheWarmer(
        Console\Input\InputInterface $input,
        Crawler\CrawlerInterface $crawler,
    ): CacheWarmer {
        if ($this->formatter->isVerbose()) {
            $this->io->write('Parsing sitemaps... ');
        }

        // Initialize cache warmer
        $cacheWarmer = new CacheWarmer(
            (int) $input->getOption('limit'),
            $this->client,
            $crawler,
            !$input->getOption('allow-failures'),
        );

        // Add and parse XML sitemaps
        $cacheWarmer->addSitemaps([...$input->getArgument('sitemaps')]);

        // Add URLs
        foreach ($input->getOption('urls') as $url) {
            $cacheWarmer->addUrl($url);
        }

        if ($this->formatter->isVerbose()) {
            $this->io->writeln('<info>Done</info>');
        }

        return $cacheWarmer;
    }

    private function initializeCrawler(Console\Input\InputInterface $input): Crawler\CrawlerInterface
    {
        /** @var class-string<Crawler\CrawlerInterface>|null $crawlerClass */
        $crawlerClass = $input->getOption('crawler');
        $crawlerOptions = $this->parseCrawlerOptions($input->getOption('crawler-options'));

        // Select default crawler
        if (null === $crawlerClass) {
            $crawlerClass = $input->getOption('progress')
                ? Crawler\OutputtingCrawler::class
                : Crawler\ConcurrentCrawler::class
            ;
        }

        // Initialize crawler
        $crawler = $this->crawlerFactory->get($crawlerClass, $crawlerOptions);

        // Print crawler options
        if ($crawler instanceof Crawler\ConfigurableCrawlerInterface) {
            if ($this->formatter->isVerbose() && $this->io->isVerbose() && [] !== $crawlerOptions) {
                $this->io->section('Using custom crawler options:');
                $this->io->writeln((string) json_encode($crawlerOptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->io->newLine();
            }
        } elseif ([] !== $crawlerOptions) {
            $this->formatter->logMessage(
                'You passed crawler options for a non-configurable crawler.',
                Formatter\MessageSeverity::Warning,
            );
        }

        return $crawler;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseCrawlerOptions(mixed $crawlerOptions): array
    {
        if (null === $crawlerOptions) {
            return [];
        }

        if (is_array($crawlerOptions)) {
            return $crawlerOptions;
        }

        if (is_string($crawlerOptions)) {
            $crawlerOptions = json_decode($crawlerOptions, true);
        }

        if (!is_array($crawlerOptions)) {
            throw new Console\Exception\RuntimeException('The given crawler options are invalid. Please pass crawler options as JSON-encoded array.', 1659120649);
        }

        return $crawlerOptions;
    }

    private function validateSitemap(?string $input): ?Sitemap\Sitemap
    {
        if (null === $input) {
            return null;
        }

        return new Sitemap\Sitemap(new Psr7\Uri($input));
    }
}
