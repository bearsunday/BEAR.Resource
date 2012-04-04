<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use Ray\Di\InjectorInterface;
use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Provider;
use BEAR\Resource\Exception;

/**
 * Page resource (page:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("prototype")
 */
class Page extends App
{
    /**
     * Class config
     *
     * @var array
     */
    public $config = array(self::CONFIG_RO_FOLDER => 'Page');
}
