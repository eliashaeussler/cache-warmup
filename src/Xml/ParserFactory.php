<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CacheWarmup\Xml;

use EliasHaeussler\CacheWarmup\DependencyInjection;
use EliasHaeussler\CacheWarmup\Event;
use EliasHaeussler\CacheWarmup\Exception;
use Psr\EventDispatcher;

use function class_exists;
use function is_subclass_of;

/**
 * ParserFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class ParserFactory
{
    public function __construct(
        private DependencyInjection\ContainerFactory $containerFactory,
    ) {}

    /**
     * @param class-string<Parser> $parserClass
     * @param array<string, mixed> $options
     *
     * @throws Exception\ParserDoesNotExist
     * @throws Exception\ParserIsInvalid
     */
    public function get(string $parserClass, array $options = []): Parser
    {
        self::validate($parserClass);

        $container = $this->containerFactory->buildFor($parserClass);
        /** @var Parser $parser */
        $parser = $container->get($parserClass);

        if ($parser instanceof ConfigurableParser) {
            $parser->setOptions($options);
        }

        $eventDispatcher = $container->get(EventDispatcher\EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new Event\ParserConstructed($parser));

        return $parser;
    }

    /**
     * @throws Exception\ParserDoesNotExist
     * @throws Exception\ParserIsInvalid
     */
    public static function validate(string $parserClass): void
    {
        if (!class_exists($parserClass)) {
            throw new Exception\ParserDoesNotExist($parserClass);
        }

        if (!is_subclass_of($parserClass, Parser::class)) {
            throw new Exception\ParserIsInvalid($parserClass);
        }
    }
}
