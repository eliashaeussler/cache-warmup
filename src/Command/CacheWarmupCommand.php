<?php

declare(strict_types=1);

namespace EliasHaeussler\CacheWarmup\Command;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@heussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler;
use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler;
use EliasHaeussler\CacheWarmup\Crawler\VerboseCrawlerInterface;
use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\CacheWarmup\Sitemap;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CacheWarmupCommand.
 *
 * @author Elias Häußler <elias@heussler.dev>
 * @license GPL-3.0-or-later
 */
class CacheWarmupCommand extends Command
{
    protected static $defaultName = 'cache-warmup';

    /**
     * @var ClientInterface|null
     */
    protected $client;

    protected function configure(): void
    {
        $this->setDescription('Warms up caches of URLs provided by a given set of XML sitemaps.');
        $this->setHelp(implode(PHP_EOL, [
            'This command can be used to warm up website caches. ',
            'It requires a set of XML sitemaps offering several URLs which will be crawled.',
            '',
            '<info>Sitemaps</info>',
            '<info>========</info>',
            'The list of sitemaps to be crawled can be defined as command argument:',
            '',
            '   <comment>bin/cache-warmup https://www.example.com/sitemap.xml</comment>',
            '',
            'You are free to crawl as many different sitemaps as you want.',
            'Alternatively, sitemaps can be specified from user input when application is in interactive mode.',
            '',
            '<info>Custom URLs</info>',
            '<info>===========</info>',
            'In addition or as an alternative to sitemaps, it\'s also possible to provide a given URL set '.
            'using the <comment>--urls</comment> option:',
            '',
            '   <comment>bin/cache-warmup -u https://www.example.com/foo -u https://www.example.com/baz</comment>',
            '',
            '<info>URL limit</info>',
            '<info>=========</info>',
            'The number of URLs to be crawled can be limited using the <comment>--limit</comment> option:',
            '',
            '   <comment>bin/cache-warmup --limit 50</comment>',
            '',
            '<info>Crawler</info>',
            '<info>=======</info>',
            'By default, cache warmup will be done using concurrent HEAD requests. ',
            'This behavior can be overridden in case a special crawler is defined using the <comment>--crawler</comment> option:',
            '',
            '   <comment>bin/cache-warmup --crawler "Vendor\Crawler\MyCrawler"</comment>',
            '',
            'It\'s up to you to ensure the given crawler class is available and fully loaded.',
            'This can best be achieved by registering the class with Composer autoloader.',
            'Also make sure the crawler implements the <comment>'.CrawlerInterface::class.'</comment> interface.',
        ]));

        $this->addArgument(
            'sitemaps',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'URLs of XML sitemaps to be used for cache warming'
        );
        $this->addOption(
            'urls',
            'u',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Custom additional URLs to be used for cache warming'
        );
        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Limit the number of URLs to be processed',
            '0'
        );
        $this->addOption(
            'progress',
            'p',
            InputOption::VALUE_NONE,
            'Show progress bar during cache warmup'
        );
        $this->addOption(
            'crawler',
            'c',
            InputOption::VALUE_OPTIONAL,
            'FQCN of the crawler to be used for cache warming'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        // Early return if sitemaps or URLs are already specified
        if ([] !== $input->getArgument('sitemaps') || [] !== $input->getOption('urls')) {
            return;
        }

        // Get sitemaps from interactive user input
        $sitemaps = [];
        $helper = $this->getHelper('question');
        do {
            $question = new Question('Please enter the URL of a XML sitemap: ');
            $sitemap = $helper->ask($input, $output, $question);
            if (null !== $sitemap) {
                $sitemaps[] = $sitemap;
                $output->writeln(sprintf('<info>Sitemap added: %s</info>', $sitemap));
            }
        } while (null !== $sitemap);

        // Throw exception if no sitemaps were added
        if ([] === $sitemaps && [] === $input->getOption('urls')) {
            throw new RuntimeException('You must enter at least one sitemap URL.', 1604258903);
        }

        $input->setArgument('sitemaps', $sitemaps);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sitemaps = $input->getArgument('sitemaps');
        $urls = $input->getOption('urls');
        $limit = (int) $input->getOption('limit');
        $io = new SymfonyStyle($input, $output);

        // Throw exception if neither sitemaps nor URLs are defined
        if ([] === $sitemaps && [] === $urls) {
            throw new RuntimeException('Neither sitemaps nor URLs are defined.', 1604261236);
        }

        // Initialize cache warmer
        $output->write('Parsing sitemaps... ');
        $cacheWarmer = new CacheWarmer($sitemaps, $limit, $this->client);
        foreach ($urls as $url) {
            $cacheWarmer->addUrl(new Uri($url));
        }
        $output->writeln('<info>Done</info>');

        // Print parsed sitemaps
        if ($output->isVeryVerbose()) {
            $decoratedSitemaps = array_map([$this, 'decorateSitemap'], $cacheWarmer->getSitemaps());
            $io->section('The following sitemaps were processed:');
            $io->listing($decoratedSitemaps);
        }

        // Print parsed URLs
        if ($output->isVeryVerbose()) {
            $io->section('The following URLs will be crawled:');
            $io->listing($cacheWarmer->getUrls());
        }

        // Initialize crawler
        $crawler = $this->initializeCrawler($input, $output);
        $isVerboseCrawler = $crawler instanceof VerboseCrawlerInterface;

        // Start crawling
        $urlCount = count($cacheWarmer->getUrls());
        $output->write(sprintf('Crawling URL%s... ', 1 === $urlCount ? '' : 's'), $isVerboseCrawler);
        $cacheWarmer->run($crawler);
        if (!$isVerboseCrawler) {
            $output->writeln('<info>Done</info>');
        }

        // Print crawler statistics
        $successfulUrls = $crawler->getSuccessfulUrls();
        $failedUrls = $crawler->getFailedUrls();
        if ($output->isVerbose()) {
            if ([] !== $successfulUrls) {
                $io->section('The following URLs were successfully crawled:');
                $io->listing($this->decorateCrawledUrls($successfulUrls));
            }
            if ([] !== $failedUrls) {
                $io->section('The following URLs failed during crawling:');
                $io->listing($this->decorateCrawledUrls($failedUrls));
            }
        }

        // Print crawler results
        if ([] !== $successfulUrls) {
            $countSuccessfulUrls = count($successfulUrls);
            $io->success(
                sprintf(
                    'Successfully warmed up caches for %d URL%s.',
                    $countSuccessfulUrls,
                    1 === $countSuccessfulUrls ? '' : 's'
                )
            );
        }
        if ([] !== $failedUrls) {
            $countFailedUrls = count($failedUrls);
            $io->error(
                sprintf(
                    'Failed to warm up caches for %d URL%s.',
                    $countFailedUrls,
                    1 === $countFailedUrls ? '' : 's'
                )
            );
        }

        return [] === $failedUrls ? 0 : 1;
    }

    protected function initializeCrawler(InputInterface $input, OutputInterface $output): CrawlerInterface
    {
        $crawler = $input->getOption('crawler');

        if (is_string($crawler)) {
            // Use crawler specified by --crawler option
            if (!class_exists($crawler)) {
                throw new RuntimeException('The specified crawler class does not exist.', 1604261816);
            }
            if (!in_array(CrawlerInterface::class, class_implements($crawler) ?: [])) {
                throw new RuntimeException('The specified crawler is not valid.', 1604261885);
            }
            /** @var CrawlerInterface $crawler */
            $crawler = new $crawler();
        } elseif ($output->isVerbose() || $input->getOption('progress')) {
            // Use default verbose crawler
            $crawler = new OutputtingCrawler();
        } else {
            // Use default crawler
            return new ConcurrentCrawler();
        }

        if ($crawler instanceof VerboseCrawlerInterface) {
            $crawler->setOutput($output);
        }

        return $crawler;
    }

    protected function decorateSitemap(Sitemap $sitemap): string
    {
        return (string) $sitemap->getUri();
    }

    /**
     * @param CrawlingState[] $crawledUrls
     *
     * @return string[]
     */
    protected function decorateCrawledUrls(array $crawledUrls): array
    {
        $urls = [];
        foreach ($crawledUrls as $crawlingState) {
            $urls[] = (string) $crawlingState->getUri();
        }

        return $urls;
    }

    public function setClient(?ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }
}
