<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * Signal
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Resource
 * @subpackage Annotation
 */
final class Signal implements Annotation
{
    /**
     * @var string
     */
    public $value;
}
