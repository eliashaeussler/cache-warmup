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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Normalizer;

use EliasHaeussler\CacheWarmup\Normalizer;
use EliasHaeussler\CacheWarmup\Sitemap;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;
use Symfony\Component\Serializer;

use function sprintf;

/**
 * UriDenormalizerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UriDenormalizerTest extends Framework\TestCase
{
    private Normalizer\UriDenormalizer $subject;

    protected function setUp(): void
    {
        $this->subject = new Normalizer\UriDenormalizer();
    }

    /**
     * @test
     */
    public function denormalizeThrowsExceptionIfGivenDataIsNotAString(): void
    {
        $this->expectException(Serializer\Exception\NotNormalizableValueException::class);
        $this->expectExceptionMessage(sprintf('The data is not a valid "%s" string representation.', Message\UriInterface::class));

        $this->subject->denormalize(null, Message\UriInterface::class);
    }

    /**
     * @test
     *
     * @dataProvider denormalizeReturnsDenormalizedUriDataProvider
     *
     * @param class-string<Message\UriInterface> $type
     */
    public function denormalizeReturnsDenormalizedUri(string $type, Message\UriInterface $expected): void
    {
        self::assertEquals($expected, $this->subject->denormalize('https://www.example.com', $type));
    }

    /**
     * @test
     */
    public function supportsDenormalizationReturnsFalseIfGivenDataIsNotAString(): void
    {
        self::assertFalse($this->subject->supportsDenormalization(null, Message\UriInterface::class));
    }

    /**
     * @test
     *
     * @dataProvider supportsDenormalizationChecksIfGivenTypeIsSupportedDataProvider
     *
     * @param class-string<Message\UriInterface> $type
     */
    public function supportsDenormalizationChecksIfGivenTypeIsSupported(string $type, bool $expected): void
    {
        self::assertSame($expected, $this->subject->supportsDenormalization('foo', $type));
    }

    /**
     * @return Generator<string, array{class-string<Message\UriInterface>, Message\UriInterface}>
     */
    public function denormalizeReturnsDenormalizedUriDataProvider(): Generator
    {
        $url = 'https://www.example.com';

        yield Message\UriInterface::class => [Message\UriInterface::class, new Psr7\Uri($url)];
        yield Psr7\Uri::class => [Psr7\Uri::class, new Psr7\Uri($url)];
    }

    /**
     * @return Generator<string, array{class-string<Message\UriInterface>, bool}>
     */
    public function supportsDenormalizationChecksIfGivenTypeIsSupportedDataProvider(): Generator
    {
        yield Message\UriInterface::class => [Message\UriInterface::class, true];
        yield Psr7\Uri::class => [Psr7\Uri::class, true];
        yield Sitemap\Url::class => [Sitemap\Url::class, false];
    }
}
