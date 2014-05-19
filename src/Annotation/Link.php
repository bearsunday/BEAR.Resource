<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Link implements AnnotationInterface
{
    const REL = 'rel';

    const SRC = 'src';

    const HREF = 'href';

    const TITLE = 'title';

    const TEMPLATED = 'templated';

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

    /**
     * Embed resource uri
     *
     * @var string
     */
    public $src;
}
