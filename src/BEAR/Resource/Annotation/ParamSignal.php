<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * ParamSignal
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Resource
 * @subpackage Annotation
 */
final class ParamSignal implements AnnotationInterface
{
    /**
     * @var string
     */
    public $value;
}
