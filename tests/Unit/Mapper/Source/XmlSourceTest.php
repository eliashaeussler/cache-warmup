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

namespace EliasHaeussler\CacheWarmup\Tests\Unit\Mapper\Source;

use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\CacheWarmup\Mapper;
use PHPUnit\Framework;

use function dirname;
use function file_get_contents;
use function iterator_to_array;

/**
 * XmlSourceTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class XmlSourceTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function fromXmlThrowsExceptionIfGivenXmlIsMalformed(): void
    {
        $this->expectException(Exception\MalformedXmlException::class);
        $this->expectExceptionCode(1670962571);
        $this->expectExceptionMessageMatches('/^The string "foo" does not contain valid XML/');

        Mapper\Source\XmlSource::fromXml('foo');
    }

    #[Framework\Attributes\Test]
    public function fromXmlConvertsXmlToArray(): void
    {
        $subject = Mapper\Source\XmlSource::fromXml($this->readFixtureFile('valid_sitemap_5'));

        $expected = [
            'url' => [
                [
                    'loc' => 'https://www.example.org/',
                    'priority' => '0.8',
                    'lastmod' => '2022-05-02T12:14:44+02:00',
                    'changefreq' => 'yearly',
                ],
                [
                    'loc' => 'https://www.example.org/foo',
                    'priority' => '0.5',
                    'lastmod' => '2021-06-07T20:01:25+02:00',
                    'changefreq' => 'monthly',
                ],
                [
                    'loc' => 'https://www.example.org/baz',
                    'priority' => '0.5',
                    'lastmod' => '2021-05-28T11:54:00+02:00',
                    'changefreq' => 'HOURLY',
                ],
            ],
        ];

        self::assertSame($expected, iterator_to_array($subject));
    }

    #[Framework\Attributes\Test]
    public function asCollectionConvertsSingleItemNodesToCollections(): void
    {
        $subject = Mapper\Source\XmlSource::fromXml($this->readFixtureFile('valid_sitemap_1'));

        self::assertSame(
            [
                'sitemap' => [
                    'loc' => 'https://www.example.org/sitemap_en.xml',
                ],
            ],
            iterator_to_array($subject),
        );

        self::assertSame(
            [
                'sitemap' => [
                    [
                        'loc' => 'https://www.example.org/sitemap_en.xml',
                    ],
                ],
            ],
            iterator_to_array($subject->asCollection('sitemap')),
        );
    }

    private function readFixtureFile(string $fixture): string
    {
        $fixtureFile = dirname(__DIR__, 2).'/Fixtures/'.$fixture.'.xml';

        self::assertFileExists($fixtureFile);

        $xml = file_get_contents($fixtureFile);

        self::assertIsString($xml);

        return $xml;
    }
}
