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

namespace EliasHaeussler\CacheWarmup\DependencyInjection;

use EliasHaeussler\CacheWarmup\Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

use function class_exists;
use function in_array;

/**
 * Container.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class Container
{
    /**
     * @var array<class-string, object>
     */
    private array $serviceBag = [];

    /**
     * @var array<class-string, true>
     */
    private array $currentlyBuilding = [];

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @throws Exception\ClassCannotBeReflected
     * @throws Exception\ClassConstructorIsInaccessible
     * @throws Exception\ClassDoesNotExist
     * @throws Exception\ParameterCannotBeAutowired
     * @throws Exception\RecursionInServiceCreation
     */
    public function get(string $className): object
    {
        /* @phpstan-ignore return.type */
        return $this->serviceBag[$className] ?? $this->serviceBag[$className] = $this->constructNewService($className);
    }

    /**
     * @param class-string $className
     */
    public function has(string $className): bool
    {
        try {
            $this->get($className);
        } catch (Exception\ClassCannotBeReflected|Exception\ClassConstructorIsInaccessible|Exception\ClassDoesNotExist|Exception\ParameterCannotBeAutowired) {
            return false;
        }

        return true;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     * @param T               $service
     */
    public function set(string $className, object $service): self
    {
        if (!isset($this->serviceBag[$className])) {
            $this->serviceBag[$className] = $service;
        }

        if ($className !== $service::class) {
            $this->serviceBag[$service::class] = $service;
        }

        return $this;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @throws Exception\ClassCannotBeReflected
     * @throws Exception\ClassConstructorIsInaccessible
     * @throws Exception\ClassDoesNotExist
     * @throws Exception\ParameterCannotBeAutowired
     * @throws Exception\RecursionInServiceCreation
     */
    private function constructNewService(string $className): object
    {
        if (isset($this->currentlyBuilding[$className])) {
            throw new Exception\RecursionInServiceCreation($className);
        }

        if (!class_exists($className)) {
            throw new Exception\ClassDoesNotExist($className);
        }

        // Flag to avoid recursion
        $this->currentlyBuilding[$className] = true;

        try {
            $reflection = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            // Return empty instance if no constructor exists
            if (null === $constructor) {
                return $reflection->newInstance();
            }

            // Throw exception on private constructor
            if (!$constructor->isPublic()) {
                throw new Exception\ClassConstructorIsInaccessible($className);
            }

            // Autowire constructor parameters
            $parameters = [];
            foreach ($constructor->getParameters() as $parameter) {
                $parameters[] = $this->autowireParameter($parameter, $className);
            }

            return $reflection->newInstanceArgs($parameters);
        } catch (ReflectionException $exception) {
            throw new Exception\ClassCannotBeReflected($className, $exception);
        } finally {
            // Free from recursion check
            unset($this->currentlyBuilding[$className]);
        }
    }

    /**
     * @param class-string $className
     *
     * @throws Exception\ClassCannotBeReflected
     * @throws Exception\ClassConstructorIsInaccessible
     * @throws Exception\ClassDoesNotExist
     * @throws Exception\ParameterCannotBeAutowired
     * @throws Exception\RecursionInServiceCreation
     * @throws ReflectionException
     */
    private function autowireParameter(ReflectionParameter $parameter, string $className): mixed
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $name = $type->getName();

            // Resolve self-references
            if (in_array($name, ['self', 'static'], true)) {
                $name = $className;
            }

            /* @phpstan-ignore argument.type */
            if ($this->has($name)) {
                /* @phpstan-ignore argument.type, argument.templateType */
                return $this->get($type->getName());
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new Exception\ParameterCannotBeAutowired($className, $parameter->getName());
    }
}
