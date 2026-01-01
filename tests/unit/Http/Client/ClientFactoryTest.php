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

namespace EliasHaeussler\CacheWarmup\Tests\Http\Client;

use EliasHaeussler\CacheWarmup as Src;
use EliasHaeussler\CacheWarmup\Tests;
use EliasHaeussler\DeepClosureComparator;
use GuzzleHttp\Client;
use PHPUnit\Framework;

/**
 * ClientFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Client\ClientFactory::class)]
final class ClientFactoryTest extends Framework\TestCase
{
    private Tests\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Src\Http\Client\ClientFactory $subject;

    public function setUp(): void
    {
        $this->eventDispatcher = new Tests\Fixtures\Classes\DummyEventDispatcher();
        $this->subject = new Src\Http\Client\ClientFactory($this->eventDispatcher, ['foo' => 'baz']);
    }

    #[Framework\Attributes\Test]
    public function getReturnsClientWithDefaultConfig(): void
    {
        $expected = new Client([
            'foo' => 'baz',
        ]);

        DeepClosureComparator\DeepClosureAssert::assertEquals($expected, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getReturnsClientWithMergedConfig(): void
    {
        $expected = new Client([
            'foo' => 'baz',
            'another' => 'foo',
        ]);

        DeepClosureComparator\DeepClosureAssert::assertEquals($expected, $this->subject->get(['another' => 'foo']));
    }

    #[Framework\Attributes\Test]
    public function getDispatchesClientConstructedEvent(): void
    {
        $this->subject->get();

        self::assertTrue(
            $this->eventDispatcher->wasDispatched(Src\Event\Http\ClientConstructed::class),
        );
    }
}
