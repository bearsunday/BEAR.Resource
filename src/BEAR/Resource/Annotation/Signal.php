<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * Signal
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Signal implements AnnotationInterface
{
    /**
     * @var string
     */
    public $value;
}
