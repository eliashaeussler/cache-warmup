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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Crawler;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Result;
use EliasHaeussler\CacheWarmup\Tests;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;
use Symfony\Component\Console;

/**
 * OutputtingCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Crawler\OutputtingCrawler::class)]
final class OutputtingCrawlerTest extends Framework\TestCase
{
    use Tests\Unit\CacheWarmupResultProcessorTrait;
    use Tests\Unit\ClientMockTrait;

    private Console\Output\BufferedOutput $output;
    private Crawler\OutputtingCrawler $subject;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->output = new Console\Output\BufferedOutput();
        $this->subject = new Crawler\OutputtingCrawler(client: $this->client);
        $this->subject->setOutput($this->output);
    }

    #[Framework\Attributes\Test]
    public function constructorInstantiatesClientWithGivenClientConfig(): void
    {
        $this->mockHandler->append(new Psr7\Response());

        $subject = new Crawler\OutputtingCrawler(
            [
                'client_config' => [
                    'handler' => $this->mockHandler,
                ],
            ],
        );
        $subject->setOutput($this->output);

        self::assertNull($this->mockHandler->getLastRequest());

        $subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertNotNull($this->mockHandler->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function constructorIgnoresGivenClientConfigIfInstantiatedClientIsPassed(): void
    {
        $subject = new Crawler\OutputtingCrawler(
            [
                'client_config' => [
                    'handler' => $this->mockHandler,
                ],
            ],
            new Client(),
        );
        $subject->setOutput($this->output);

        self::assertNull($this->mockHandler->getLastRequest());

        $subject->crawl([new Psr7\Uri('https://www.example.org')]);

        self::assertNull($this->mockHandler->getLastRequest());
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

        $subject = new Crawler\OutputtingCrawler(
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

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Successful));
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

        self::assertSame($urls, $this->getProcessedUrlsFromCacheWarmupResult($result, Result\CrawlingState::Failed));
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
        self::assertMatchesRegularExpression('#^\s*1/2 \S+\s+\d+% -- no failures$#m', $output);
        self::assertMatchesRegularExpression('#^\s*2/2 \S+\s+\d+% -- 1 failure$#m', $output);
    }
}
