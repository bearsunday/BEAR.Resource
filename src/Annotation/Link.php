<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Link
{
    /**
     * @var string
     */
    public $crawl = '';

    /**
     * Relation
     *
     * @var string
     */
    public $rel;

    /**
     * Hyper reference uri
     *
     * @var string
     */
    public $href;

    /**
     * Request method
     *
     * @var string
     */
    public $method = 'get';
}
