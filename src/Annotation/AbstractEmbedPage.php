<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

/**
 * Base of a page-kind class attribute: onGet() of the annotated resource finds these parts
 * at the top level of the body as requests, as #[Embed] puts them.
 */
abstract class AbstractEmbedPage
{
    /**
     * @return array<string, string> rel => URI template, expanded with the onGet() arguments
     *
     * @psalm-mutation-free
     */
    abstract public function embed(): array;
}
