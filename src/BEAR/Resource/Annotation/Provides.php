<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * Provides
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Provides implements AnnotationInterface
{
    /**
     * @var string
     */
    public $value;
}
