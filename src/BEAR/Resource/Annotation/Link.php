<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * Link
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Link implements AnnotationInterface
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
     * Hyper reference
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
