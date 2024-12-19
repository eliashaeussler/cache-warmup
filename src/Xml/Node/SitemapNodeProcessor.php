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

namespace EliasHaeussler\CacheWarmup\Xml\Node;

use EliasHaeussler\CacheWarmup\Exception;
use Netlogix\XmlProcessor;
use XMLReader;

use function array_map;
use function in_array;

/**
 * SitemapNodeProcessor.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
final class SitemapNodeProcessor implements XmlProcessor\NodeProcessor\NodeProcessorInterface, XmlProcessor\NodeProcessor\TextNodeProcessorInterface, XmlProcessor\NodeProcessor\CloseNodeProcessorInterface
{
    /**
     * @var list<array<string, string>>
     */
    private array $processedNodes = [];

    /**
     * @var array<string, string>
     */
    private array $currentNode = [];

    /**
     * @param list<SitemapNode> $supportedNodes
     */
    public function __construct(
        private readonly SitemapNodePath $baseNodePath,
        private readonly array $supportedNodes,
    ) {}

    /**
     * @return iterable<string, callable>
     */
    public function getSubscribedEvents(string $nodePath, XmlProcessor\XmlProcessorContext $context): iterable
    {
        if ($this->isSupportedNodePath($nodePath)) {
            yield XmlProcessor\XmlProcessor::EVENT_OPEN_FILE => $this->reset(...);
            yield 'NodeType_'.XMLReader::TEXT => $this->textElement(...);
            yield 'NodeType_'.XMLReader::END_ELEMENT => $this->closeElement(...);
        }

        yield from [];
    }

    public function textElement(XmlProcessor\NodeProcessor\Context\TextContext $context): void
    {
        $node = SitemapNode::tryFromPath($context->getNodePath(), $this->baseNodePath);

        if (null !== $node && in_array($node, $this->supportedNodes, true)) {
            $this->currentNode[$node->value] = $context->getText();
        }
    }

    /**
     * @throws Exception\XmlNodeIsEmpty
     */
    public function closeElement(XmlProcessor\NodeProcessor\Context\CloseContext $context): void
    {
        // Early return if unsupported node is processed
        if ($context->getNodePath() !== $this->baseNodePath->value) {
            return;
        }

        // Throw exception if processed node is empty
        if ([] === $this->currentNode) {
            throw new Exception\XmlNodeIsEmpty($this->baseNodePath->value);
        }

        $this->processedNodes[] = $this->currentNode;
        $this->currentNode = [];
    }

    public function reset(): void
    {
        $this->processedNodes = [];
        $this->currentNode = [];
    }

    /**
     * @return list<array<string, string>>
     */
    public function getProcessedNodes(): array
    {
        return $this->processedNodes;
    }

    private function isSupportedNodePath(string $nodePath): bool
    {
        $supportedNodePaths = [
            $this->baseNodePath->value,
            ...array_map(fn (SitemapNode $node) => $node->asPath($this->baseNodePath), $this->supportedNodes),
        ];

        foreach ($supportedNodePaths as $expected) {
            if (XmlProcessor\XmlProcessor::checkNodePath($nodePath, $expected)) {
                return true;
            }
        }

        return false;
    }
}
