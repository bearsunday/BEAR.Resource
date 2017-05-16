<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @deprecated use OptionsRender instead
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
