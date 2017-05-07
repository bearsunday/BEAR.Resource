<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\DocBlockFactory;

/** @noinspection PhpInconsistentReturnPointsInspection */

/**
 * @deprecated
 */
final class OptionProvider implements OptionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(ResourceObject $ro)
    {
        (new OptionsRenderer(new AnnotationReader()))->render($ro);
    }
}
