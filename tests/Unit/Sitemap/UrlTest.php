<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Sitemap;

use DateTimeImmutable;
use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Sitemap;
use PHPUnit\Framework;

/**
 * UrlTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UrlTest extends Framework\TestCase
{
    /**
     * @test
     */
    public function constructorThrowsExceptionIfGivenUriIsEmpty(): void
    {
        $this->expectException(Exception\InvalidUrlException::class);
        $this->expectExceptionCode(1604055264);
        $this->expectExceptionMessage('The given URL must not be empty.');

        new Sitemap\Url('');
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfGivenUriIsNotValid(): void
    {
        $this->expectException(Exception\InvalidUrlException::class);
        $this->expectExceptionCode(1604055334);
        $this->expectExceptionMessage('The given URL "foo" is not valid.');

        new Sitemap\Url('foo');
    }

    /**
     * @test
     */
    public function constructorAssignsUriCorrectly(): void
    {
        $subject = new Sitemap\Url('https://foo.baz');

        self::assertSame($subject, $subject->getUri());
    }

    /**
     * @test
     */
    public function constructorAssignsPriorityCorrectly(): void
    {
        $priority = 0.8;
        $subject = new Sitemap\Url('https://foo.baz', priority: $priority);

        self::assertSame($priority, $subject->getPriority());
    }

    /**
     * @test
     */
    public function constructorAssignsLastModificationDateCorrectly(): void
    {
        $lastModificationDate = (new DateTimeImmutable())->modify('- 1 day');
        $subject = new Sitemap\Url('https://foo.baz', lastModificationDate: $lastModificationDate);

        self::assertSame($lastModificationDate, $subject->getLastModificationDate());
    }

    /**
     * @test
     */
    public function constructorAssignsChangeFrequencyCorrectly(): void
    {
        $changeFrequency = Sitemap\ChangeFrequency::Hourly;
        $subject = new Sitemap\Url('https://foo.baz', changeFrequency: $changeFrequency);

        self::assertSame($changeFrequency, $subject->getChangeFrequency());
    }
}
