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

namespace EliasHaeussler\CacheWarmup\Command;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Config;
use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Event;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Formatter;
use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Log;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\CacheWarmup\Time;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console;
use Symfony\Component\EventDispatcher;
use Symfony\Component\Filesystem;

use function array_map;
use function array_unshift;
use function count;
use function getenv;
use function implode;
use function in_array;
use function is_string;
use function json_encode;
use function sleep;
use function sprintf;
use function strtolower;

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

    private readonly Time\TimeTracker $timeTracker;
    private Config\CacheWarmupConfig $config;
    private Console\Style\SymfonyStyle $io;
    private Formatter\Formatter $formatter;
    private Crawler\CrawlerFactory $crawlerFactory;
    private bool $firstRun = true;

    public function __construct(
        private readonly ClientInterface $client = new Client(),
        private readonly EventDispatcherInterface $eventDispatcher = new EventDispatcher\EventDispatcher(),
    ) {
        parent::__construct('cache-warmup');
        $this->timeTracker = new Time\TimeTracker();
    }

    protected function configure(): void
    {
        $crawler = Crawler\Crawler::class;
        $configurableCrawler = Crawler\ConfigurableCrawler::class;
        $stoppableCrawler = Crawler\StoppableCrawler::class;
        $textFormatter = Formatter\TextFormatter::getType();
        $jsonFormatter = Formatter\JsonFormatter::getType();
        $sortByChangeFrequencyStrategy = Crawler\Strategy\SortByChangeFrequencyStrategy::getName();
        $sortByLastModificationDateStrategy = Crawler\Strategy\SortByLastModificationDateStrategy::getName();
        $sortByPriorityStrategy = Crawler\Strategy\SortByPriorityStrategy::getName();
        $logLevels = implode(
            PHP_EOL,
            array_map(
                static fn (string $logLevel): string => '   * <comment>'.strtolower($logLevel).'</comment>',
                Log\LogLevel::getAll(),
            ),
        );

        $this->setDescription('Warms up caches of URLs provided by a given set of XML sitemaps.');
        $this->setHelp(<<<HELP
This command can be used to warm up website caches.
It requires a set of XML sitemaps offering several URLs which will be crawled.

<info>Sitemaps</info>
<info>========</info>
The list of sitemaps to be crawled can be defined as command argument:

   * <comment>%command.full_name% https://www.example.com/sitemap.xml</comment> (URL)
   * <comment>%command.full_name% /var/www/html/sitemap.xml</comment> (local file)

You are free to crawl as many sitemaps as you want.
Alternatively, sitemaps can be specified from user input when application is in interactive mode.

<info>Custom URLs</info>
<info>===========</info>
In addition or as an alternative to sitemaps, it's also possible to provide a given URL set using the <comment>--urls</comment> option:

   <comment>%command.full_name% -u https://www.example.com/foo -u https://www.example.com/baz</comment>

<info>Config file</info>
<info>===========</info>
All command parameters can be configured in an external config file.
Use the <comment>--config</comment> option to specify the config file:

   <comment>%command.full_name% -c cache-warmup.php</comment>

The following formats are currently supported:

   * <comment>json</comment>
   * <comment>php</comment>
   * <comment>yaml/yml</comment>

<info>Exclude patterns</info>
<info>================</info>
You can specify exclude patterns to be applied on URLs in order to ignore them from cache warming.
Use the <comment>--exclude</comment> (or <comment>-e</comment>) option to specify one or more patterns:

   <comment>%command.full_name% -e "*no_cache=1*" -e "*no_warming=1*"</comment>

You can also specify regular expressions as exclude patterns.
Note that each expression must start and end with a <comment>#</comment> symbol:

   <comment>%command.full_name% -e "#(no_cache|no_warming)=1#"</comment>

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
Also make sure the crawler implements <comment>{$crawler}</comment>.

<info>Crawler options</info>
<info>===============</info>
For crawlers implementing <comment>{$configurableCrawler}</comment>,
it is possible to pass a JSON-encoded string of crawler options by using the <comment>--crawler-options</comment> option:

   <comment>%command.full_name% --crawler-options '{"concurrency": 3}'</comment>

<info>Crawling strategy</info>
<info>=================</info>
URLs can be crawled using a specific crawling strategy, e.g. by sorting them by a specific property.
For this, use the <comment>--strategy</comment> option together with a predefined value:

   <comment>%command.full_name% --strategy {$sortByPriorityStrategy}</comment>

The following strategies are currently available:

   * <comment>{$sortByChangeFrequencyStrategy}</comment>
   * <comment>{$sortByLastModificationDateStrategy}</comment>
   * <comment>{$sortByPriorityStrategy}</comment>

<info>Allow failures</info>
<info>==============</info>
If a sitemap cannot be parsed or a URL fails to be crawled, this command normally exits
with a non-zero exit code. This is not always the desired behavior. Therefore, you can change
this behavior by using the <comment>--allow-failures</comment> option:

   <comment>%command.full_name% --allow-failures</comment>

<info>Stop on failure</info>
<info>===============</info>
For crawlers implementing <comment>{$stoppableCrawler}</comment>,
you can also configure the crawler to stop on failure. The <comment>--stop-on-failure</comment> option
exists for this case:

   <comment>%command.full_name% --stop-on-failure</comment>

<info>Format output</info>
<info>=============</info>
By default, all user-oriented output is printed as plain text to the console.
However, you can use other formatters by using the <comment>--format</comment> option:

   <comment>%command.full_name% --format json</comment>

Currently, the following formatters are available:

   * <comment>{$textFormatter}</comment> (default)
   * <comment>{$jsonFormatter}</comment>

<info>Logging</info>
<info>=======</info>
You can log the crawling results of each crawled URL to an external log file.
For this, the <comment>--log-file</comment> option exists:

   <comment>%command.full_name% --log-file crawling-errors.log</comment>

When logging is enabled, by default only crawling failures are logged.
You can increase the log level to log successful crawlings as well:

   * <comment>%command.full_name% --log-level error</comment> (default)
   * <comment>%command.full_name% --log-level info</comment>

The following log levels are currently available:

{$logLevels}

HELP);

        $this->addArgument(
            'sitemaps',
            Console\Input\InputArgument::OPTIONAL | Console\Input\InputArgument::IS_ARRAY,
            'URLs or local filenames of XML sitemaps to be used for cache warming',
        );
        $this->addOption(
            'urls',
            'u',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Custom additional URLs to be used for cache warming',
        );
        $this->addOption(
            'config',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'Path to configuration file',
        );
        $this->addOption(
            'exclude',
            'e',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Patterns for URLs to be excluded from cache warming',
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
            'strategy',
            's',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Optional strategy to prepare URLs before crawling them',
        );
        $this->addOption(
            'allow-failures',
            null,
            Console\Input\InputOption::VALUE_NONE,
            'Allow failures during URL crawling and exit with zero',
        );
        $this->addOption(
            'stop-on-failure',
            null,
            Console\Input\InputOption::VALUE_NONE,
            'Cancel further cache warmup requests on failure',
        );
        $this->addOption(
            'format',
            'f',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Formatter used to print the cache warmup result',
            Formatter\TextFormatter::getType(),
        );
        $this->addOption(
            'log-file',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'File where to log crawling results',
        );
        $this->addOption(
            'log-level',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'Log level used to determine which crawling results to log (see help for more information)',
            LogLevel::ERROR,
        );
        $this->addOption(
            'repeat-after',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'Run cache warmup in endless loop and repeat x seconds after each run',
            0,
        );
    }

    /**
     * @throws Exception\ConfigFileIsNotSupported
     * @throws Exception\FormatterIsNotSupported
     * @throws Exception\LogLevelIsNotSupported
     */
    protected function initialize(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): void
    {
        $configFile = $input->getOption('config');
        $configFileFromEnv = getenv('CACHE_WARMUP_CONFIG');
        $configAdapters = [
            new Config\Adapter\ConsoleInputConfigAdapter($input),
            new Config\Adapter\EnvironmentVariablesConfigAdapter(),
        ];

        if (false !== $configFileFromEnv) {
            array_unshift($configAdapters, $this->loadConfigFromFile($configFileFromEnv));
        }
        if (null !== $configFile) {
            array_unshift($configAdapters, $this->loadConfigFromFile($configFile));
        }

        $this->config = (new Config\Adapter\CompositeConfigAdapter($configAdapters))->get();
        $this->eventDispatcher->dispatch(new Event\ConfigResolved($this->config));

        $this->io = new Console\Style\SymfonyStyle($input, $output);
        $this->formatter = (new Formatter\FormatterFactory($this->io))->get($this->config->getFormat());

        $logFile = $this->config->getLogFile();
        $logLevel = $this->config->getLogLevel();
        $stopOnFailure = $this->config->shouldStopOnFailure();
        $logger = null;

        // Create logger
        if (is_string($logFile)) {
            $logger = new Log\FileLogger($logFile);
        }

        // Validate log level
        if (!in_array($logLevel, Log\LogLevel::getAll(), true)) {
            throw new Exception\LogLevelIsNotSupported($logLevel);
        }

        // Use error output or disable output if formatter is non-verbose
        if (!$this->formatter->isVerbose()) {
            if ($output instanceof Console\Output\ConsoleOutputInterface) {
                $output = $output->getErrorOutput();

                $this->config->enableProgressBar();
            } else {
                $output = new Console\Output\NullOutput();
            }
        }

        $this->crawlerFactory = new Crawler\CrawlerFactory(
            $output,
            $logger,
            $logLevel,
            $stopOnFailure,
            $this->eventDispatcher,
        );
    }

    protected function interact(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): void
    {
        // Early return if sitemaps or URLs are already specified
        if ([] !== $this->config->getSitemaps() || [] !== $this->config->getUrls()) {
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

        $this->config->setSitemaps($sitemaps);
    }

    /**
     * @throws Exception\ConfigFileIsNotSupported
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $sitemaps = $this->config->getSitemaps();
        $urls = $this->config->getUrls();
        $repeatAfter = $this->config->getRepeatAfter();

        // Throw exception if neither sitemaps nor URLs are defined
        if ([] === $sitemaps && [] === $urls) {
            throw new Console\Exception\RuntimeException('Neither sitemaps nor URLs are defined.', 1604261236);
        }

        // Show header
        if ($this->formatter->isVerbose()) {
            $this->printHeader();
        }

        // Show warning on endless runs
        if ($this->firstRun && $repeatAfter > 0) {
            $this->showEndlessModeWarning($repeatAfter);
            $this->firstRun = false;
        }

        // Initialize components
        $crawler = $this->initializeCrawler();
        $cacheWarmer = $this->timeTracker->track(fn () => $this->initializeCacheWarmer($crawler));
        $parseTime = $this->timeTracker->getLastDuration();

        // Start crawling
        $result = $this->timeTracker->track(
            fn () => $this->runCacheWarmup(
                $cacheWarmer,
                $crawler instanceof Crawler\VerboseCrawler,
            ),
        );

        // Print formatted parser result
        $this->formatter->formatParserResult(
            new Result\ParserResult($cacheWarmer->getSitemaps(), $cacheWarmer->getUrls()),
            new Result\ParserResult($cacheWarmer->getFailedSitemaps()),
            new Result\ParserResult($cacheWarmer->getExcludedSitemaps(), $cacheWarmer->getExcludedUrls()),
            $parseTime,
        );

        // Print formatted cache warmup result
        $this->formatter->formatCacheWarmupResult($result, $this->timeTracker->getLastDuration());

        // Early return if parsing or crawling failed
        if (!$this->config->areFailuresAllowed()
            && ([] !== $cacheWarmer->getFailedSitemaps() || !$result->isSuccessful())
        ) {
            return self::FAILED;
        }

        // Repeat on endless mode
        if ($repeatAfter > 0) {
            sleep($repeatAfter);

            return $this->execute($input, $output);
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
            if ($result->wasCancelled()) {
                $this->io->writeln('<comment>Cancelled</comment>');
            } else {
                $this->io->writeln('<info>Done</info>');
            }
        }

        return $result;
    }

    private function initializeCacheWarmer(Crawler\Crawler $crawler): CacheWarmer
    {
        if ($this->formatter->isVerbose()) {
            $this->io->write('Parsing sitemaps... ');
        }

        // Initialize crawling strategy
        $strategy = $this->config->getStrategy();
        if (is_string($strategy)) {
            $strategy = match ($strategy) {
                Crawler\Strategy\SortByChangeFrequencyStrategy::getName() => new Crawler\Strategy\SortByChangeFrequencyStrategy(),
                Crawler\Strategy\SortByLastModificationDateStrategy::getName() => new Crawler\Strategy\SortByLastModificationDateStrategy(),
                Crawler\Strategy\SortByPriorityStrategy::getName() => new Crawler\Strategy\SortByPriorityStrategy(),
                default => throw new Console\Exception\RuntimeException('The given crawling strategy is invalid.', 1677618007),
            };
        }

        // Initialize cache warmer
        $cacheWarmer = new CacheWarmer(
            $this->config->getLimit(),
            $this->client,
            $crawler,
            $strategy,
            !$this->config->areFailuresAllowed(),
            $this->config->getExcludePatterns(),
            $this->eventDispatcher,
        );

        // Add and parse XML sitemaps
        $cacheWarmer->addSitemaps($this->config->getSitemaps());

        // Add URLs
        foreach ($this->config->getUrls() as $url) {
            $cacheWarmer->addUrl($url);
        }

        if ($this->formatter->isVerbose()) {
            $this->io->writeln('<info>Done</info>');
        }

        return $cacheWarmer;
    }

    private function initializeCrawler(): Crawler\Crawler
    {
        $crawler = $this->config->getCrawler();
        $crawlerOptions = $this->crawlerFactory->parseCrawlerOptions($this->config->getCrawlerOptions());
        $stopOnFailure = $this->config->shouldStopOnFailure();

        // Select default crawler
        if (null === $crawler) {
            $crawler = $this->config->isProgressBarEnabled()
                ? Crawler\OutputtingCrawler::class
                : Crawler\ConcurrentCrawler::class
            ;
        }

        // Initialize crawler
        if (is_string($crawler)) {
            $crawler = $this->crawlerFactory->get($crawler, $crawlerOptions);
        }

        // Print crawler options
        if ($crawler instanceof Crawler\ConfigurableCrawler) {
            if ($this->formatter->isVerbose() && $this->io->isVerbose() && [] !== $crawlerOptions) {
                $this->io->section('Using custom crawler options:');
                $this->io->writeln((string) json_encode($crawlerOptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->io->newLine();
            }
        } elseif ([] !== $crawlerOptions) {
            $this->formatter->logMessage(
                'You passed crawler options to a non-configurable crawler.',
                Formatter\MessageSeverity::Warning,
            );
        }

        // Show notice on unsupported stoppable crawler feature
        if ($stopOnFailure && !($crawler instanceof Crawler\StoppableCrawler)) {
            $this->formatter->logMessage(
                'You configured "stop on failure" for a non-stoppable crawler.',
                Formatter\MessageSeverity::Warning,
            );
        }

        return $crawler;
    }

    /**
     * @throws Exception\ConfigFileIsNotSupported
     */
    private function loadConfigFromFile(string $configFile): Config\Adapter\ConfigAdapter
    {
        $configFile = Helper\FilesystemHelper::resolveRelativePath($configFile);
        $extension = Filesystem\Path::getExtension($configFile, true);

        return match ($extension) {
            'php' => new Config\Adapter\PhpConfigAdapter($configFile),
            'json', 'yaml', 'yml' => new Config\Adapter\FileConfigAdapter($configFile),
            default => throw new Exception\ConfigFileIsNotSupported($configFile),
        };
    }

    private function showEndlessModeWarning(int $interval): void
    {
        $this->formatter->logMessage(
            sprintf(
                'Command is scheduled to run forever. It will be repeated %d second%s after each run.',
                $interval,
                1 === $interval ? '' : 's',
            ),
            Formatter\MessageSeverity::Warning,
        );
    }

    private function validateSitemap(?string $input): ?Sitemap\Sitemap
    {
        if (null === $input) {
            return null;
        }

        return Sitemap\Sitemap::createFromString($input);
    }

    private function printHeader(): void
    {
        $this->io->writeln(
            sprintf(
                'Running <info>cache warmup</info> <comment>%s</comment> by Elias Häußler and contributors.',
                CacheWarmer::VERSION,
            ),
        );
    }
}
