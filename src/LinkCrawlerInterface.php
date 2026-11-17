<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

/**
 * Interface for crawl link processing
 *
 * This interface allows different implementations for sequential (sync)
 * and parallel (async) crawl execution.
 *
 * @psalm-import-type Query from Types
 * @psalm-import-type QueryList from Types
 */
interface LinkCrawlerInterface
{
    /**
     * Process crawl links for a list of body items
     *
     * @param list<Link>                             $annotations Link annotations from the resource method
     * @param LinkType                               $link        The crawl link type
     * @param array<array-key, array<string, mixed>> $bodyList    Reference to body items to be updated with linked data
     *
     * @param-out array<array-key, array<string, mixed>> $bodyList
     */
    public function crawl(array $annotations, LinkType $link, array &$bodyList): void;

    /**
     * Determine if the value is a list (multiple rows) or a single row
     */
    public function isList(mixed $value): bool;
}
