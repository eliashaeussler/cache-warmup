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
use EliasHaeussler\CacheWarmup\Exception;
use PHPUnit\Framework;

/**
 * AbstractConfigurableCrawlerTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Crawler\AbstractConfigurableCrawler::class)]
final class AbstractConfigurableCrawlerTest extends Framework\TestCase
{
    private DummyConfigurableCrawler $subject;

    protected function setUp(): void
    {
        $this->subject = new DummyConfigurableCrawler();
    }

    #[Framework\Attributes\Test]
    public function defaultOptionsAreUsedOnInitialObject(): void
    {
        $expected = [
            'foo' => 'hello world',
            'bar' => 42,
        ];

        self::assertSame($expected, $this->subject->getOptions());
    }

    #[Framework\Attributes\Test]
    public function setOptionsThrowsExceptionIfInvalidOptionsAreGiven(): void
    {
        $this->expectException(Exception\InvalidCrawlerOptionException::class);
        $this->expectExceptionCode(1659206995);
        $this->expectExceptionMessage(
            'The crawler options "dummy" and "blub" are invalid or not supported by crawler "'.$this->subject::class.'".',
        );

        $this->subject->setOptions([
            'foo' => 'bar',
            'dummy' => 'dummy',
            'blub' => 'water',
        ]);
    }

    #[Framework\Attributes\Test]
    public function setOptionsMergesGivenOptionsWithDefaultOptions(): void
    {
        $this->subject->setOptions([
            'foo' => 'bar',
        ]);

        $expected = [
            'foo' => 'bar',
            'bar' => 42,
        ];

        self::assertSame($expected, $this->subject->getOptions());
    }
}
