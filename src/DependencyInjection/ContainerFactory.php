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

namespace EliasHaeussler\CacheWarmup\DependencyInjection;

use EliasHaeussler\CacheWarmup\Crawler;
use EliasHaeussler\CacheWarmup\Helper;
use EliasHaeussler\CacheWarmup\Http;
use EliasHaeussler\CacheWarmup\Xml;
use GuzzleHttp\ClientInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\DependencyInjection;
use Symfony\Component\EventDispatcher;

use function array_filter;

/**
 * ContainerFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class ContainerFactory
{
    /**
     * @var array<class-string, object>
     */
    private readonly array $services;

    /**
     * @var array<class-string<Crawler\Crawler|Xml\Parser>, DependencyInjection\ContainerInterface>
     */
    private array $containers = [];

    public function __construct(
        Console\Output\OutputInterface $output = new Console\Output\ConsoleOutput(),
        ?Log\LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = new EventDispatcher\EventDispatcher(),
        ?Http\Client\ClientFactory $clientFactory = null,
    ) {
        $clientFactory ??= new Http\Client\ClientFactory($eventDispatcher);

        $this->services = array_filter([
            Console\Output\OutputInterface::class => $output,
            Log\LoggerInterface::class => $logger,
            EventDispatcherInterface::class => $eventDispatcher,
            Http\Client\ClientFactory::class => $clientFactory,
            ClientInterface::class => $clientFactory->get(),
        ]);
    }

    /**
     * @param class-string<Crawler\Crawler|Xml\Parser> $className
     */
    public function buildFor(string $className): DependencyInjection\ContainerInterface
    {
        if (isset($this->containers[$className])) {
            return $this->containers[$className];
        }

        $container = new DependencyInjection\ContainerBuilder();

        // Register crawler or parser as public service
        $container->register($className)
            ->setPublic(true)
            ->setAutowired(true);

        return $this->containers[$className] = $this->buildWithServices($container);
    }

    /**
     * @codeCoverageIgnore
     */
    public function buildForTesting(): DependencyInjection\ContainerInterface
    {
        $container = new DependencyInjection\ContainerBuilder();
        $container->addCompilerPass(
            new CompilerPass\ContainerBuilderDebugDumpPass(
                Helper\FilesystemHelper::joinPathSegments(
                    Helper\FilesystemHelper::getWorkingDirectory(),
                    '.build/container.xml',
                ),
            ),
        );

        return $this->buildWithServices($container);
    }

    private function buildWithServices(DependencyInjection\ContainerBuilder $container): DependencyInjection\ContainerBuilder
    {
        // Register runtime services
        foreach ($this->services as $alias => $service) {
            $container
                ->register($service::class)
                ->setSynthetic(true)
                ->setPublic(true)
            ;

            if ($alias !== $service::class) {
                $container
                    ->setAlias($alias, $service::class)
                    ->setPublic(true)
                ;
            }
        }

        $container->compile();

        // Inject runtime services
        foreach ($this->services as $service) {
            $container->set($service::class, $service);
        }

        return $container;
    }
}
