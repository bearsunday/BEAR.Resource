<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Annotation;

/**
 * Link
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Resource
 * @subpackage Annotation
 */
final class Link
{
    public $rel;
    public $href;
    public $method = 'get';
}
