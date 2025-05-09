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

namespace EliasHaeussler\CacheWarmup\Tests\Crawler;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\TransientLogger;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use Psr\Http\Message;
use Psr\Log;
use Symfony\Component\Console;

/**
 * OutputtingCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\OutputtingCrawler::class)]
final class OutputtingCrawlerTest extends Framework\TestCase
{
    use Tests\CacheWarmupResultProcessorTrait;
    use Tests\ClientMockTrait;

    private Console\Output\BufferedOutput $output;
    private Src\Crawler\OutputtingCrawler $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Src\Crawler\OutputtingCrawler(
            ['concurrency' => 1],
            $this->client,
        );
        $this->subject->setOutput($this->output);
    }

    #[Framework\Attributes\Test]
    public function crawlDoesNotWritesResponseBodyByDefault(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $this->subject->crawl([new Psr7\Uri('https://www.example.org')]);

        $lastOptions = $this->mockHandler->getLastOptions();
        $sink = $lastOptions[RequestOptions::SINK] ?? null;

        self::assertInstanceOf(
            Src\Http\Message\Stream\NullStream::class,
            $lastOptions[RequestOptions::SINK] ?? null,
        );
    }

    #[Framework\Attributes\Test]
    public function crawlWritesResponseBodyIfConfigured(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $this->subject->setOptions([
            'write_response_body' => true,
        ]);

        $this->subject->crawl([new Psr7\Uri('https://www.example.org')]);

        $lastOptions = $this->mockHandler->getLastOptions();
        $sink = $lastOptions[RequestOptions::SINK] ?? null;

        self::assertNull($sink);
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestToAllGivenUrls(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
            new Psr7\Response(),
        );

        $urls = [
            new Psr7\Uri('https://www.example.org'),
            new Psr7\Uri('https://www.example.com'),
            new Psr7\Uri('https://www.example.net'),
            new Psr7\Uri('https://www.example.edu'),
            new Psr7\Uri('https://www.beispiel.de'),
        ];

        $result = $this->subject->crawl($urls);
        $processedUrls = $this->getProcessedUrlsFromCacheWarmupResult($result);

        self::assertSame([], array_diff($urls, $processedUrls));
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestsWithDefaultUserAgentHeader(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $this->subject->crawl($urls);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(Message\RequestInterface::class, $lastRequest);
        self::assertStringStartsWith('EliasHaeussler-CacheWarmup/', $lastRequest->getHeader('User-Agent')[0] ?? '');
    }

    #[Framework\Attributes\Test]
    public function crawlSendsRequestsWithOverriddenUserAgentHeader(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $subject = new Src\Crawler\OutputtingCrawler(
            [
                'request_headers' => [
                    'User-Agent' => 'foo',
                ],
            ],
            $this->client,
        );
        $subject->setOutput($this->output);

        $subject->crawl($urls);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(Message\RequestInterface::class, $lastRequest);
        self::assertSame(['foo'], $lastRequest->getHeader('User-Agent'));
    }

    #[Framework\Attributes\Test]
    public function crawlHandlesSuccessfulRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $urls = [
            new Psr7\Uri('https://www.example.org'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Successful));
        self::assertSame([], $result->getFailed());
    }

    #[Framework\Attributes\Test]
    public function crawlHandlesFailedRequestsOfAllGivenUrls(): void
    {
        $this->mockHandler->append(new Psr7\Response(404));

        $urls = [
            new Psr7\Uri('https://www.foo.baz'),
        ];

        $result = $this->subject->crawl($urls);

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
    }

    #[Framework\Attributes\Test]
    public function crawlWritesCrawlingStateAsProgressBarToOutput(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(404),
        );

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->crawl([$uri1, $uri2]);

        $output = $this->output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('2 / 2', $output);
    }

    #[Framework\Attributes\Test]
    public function crawlWritesCrawlingStateAsVerboseProgressBarToOutput(): void
    {
        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(404),
        );

        $output = new Tests\BufferedConsoleOutput();
        $output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_VERBOSE);

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->setOutput($output);
        $this->subject->crawl([$uri1, $uri2]);

        $output = $output->fetch();

        self::assertNotEmpty($output);
        self::assertStringContainsString('DONE  https://www.example.org', $output);
        self::assertStringContainsString('FAIL  https://www.foo.baz', $output);
    }

    #[Framework\Attributes\Test]
    public function crawlLogsCrawlingResults(): void
    {
        $logger = new TransientLogger\TransientLogger();

        $this->mockHandler->append(
            new Psr7\Response(),
            new Psr7\Response(404),
        );

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->setLogger($logger);
        $this->subject->setLogLevel(Log\LogLevel::INFO);
        $this->subject->crawl([$uri1, $uri2]);

        self::assertCount(1, $logger->getByLogLevel(TransientLogger\Log\LogLevel::Error));
        self::assertCount(1, $logger->getByLogLevel(TransientLogger\Log\LogLevel::Info));
    }

    #[Framework\Attributes\Test]
    public function crawlStopsOnFailure(): void
    {
        $this->mockHandler->append(new Psr7\Response(404));

        $uri1 = new Psr7\Uri('https://www.example.org');
        $uri2 = new Psr7\Uri('https://www.foo.baz');

        $this->subject->stopOnFailure();

        $result = $this->subject->crawl([$uri1, $uri2]);

        self::assertSame([$uri1], $this->getProcessedUrlsFromCacheWarmupResult($result, Src\Result\CrawlingState::Failed));
        self::assertSame([], $result->getSuccessful());
        self::assertTrue($result->wasCancelled());
    }
}
