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
use PHPUnit\Framework;
use Symfony\Component\Serializer;

/**
 * ChangeFrequencyNormalizerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ChangeFrequencyNormalizerTest extends Framework\TestCase
{
    private Normalizer\ChangeFrequencyNormalizer $subject;

    protected function setUp(): void
    {
        $this->subject = new Normalizer\ChangeFrequencyNormalizer();
    }

    /**
     * @test
     */
    public function denormalizeThrowsExceptionIfGivenDataIsNotAString(): void
    {
        $this->expectException(Serializer\Exception\NotNormalizableValueException::class);

        $this->subject->denormalize(null, Sitemap\ChangeFrequency::class);
    }

    /**
     * @test
     */
    public function denormalizeThrowsExceptionIfGivenDataCannotBeDenormalized(): void
    {
        $this->expectException(Serializer\Exception\NotNormalizableValueException::class);

        $this->subject->denormalize('foo', Sitemap\ChangeFrequency::class);
    }

    /**
     * @test
     *
     * @dataProvider denormalizeReturnsDenormalizedDataDataProvider
     */
    public function denormalizeReturnsDenormalizedData(string $data, Sitemap\ChangeFrequency $expected): void
    {
        self::assertSame($expected, $this->subject->denormalize($data, Sitemap\ChangeFrequency::class));
    }

    /**
     * @test
     */
    public function supportsDenormalizationReturnsTrueIfGivenTypeIsChangeFrequency(): void
    {
        self::assertTrue($this->subject->supportsDenormalization('foo', Sitemap\ChangeFrequency::class));
        self::assertFalse($this->subject->supportsDenormalization('foo', 'baz'));
    }

    /**
     * @return Generator<string, array{string, Sitemap\ChangeFrequency}>
     */
    public function denormalizeReturnsDenormalizedDataDataProvider(): Generator
    {
        yield 'supported value' => ['monthly', Sitemap\ChangeFrequency::Monthly];
        yield 'case-insensitive value' => ['Monthly', Sitemap\ChangeFrequency::Monthly];
    }
}
