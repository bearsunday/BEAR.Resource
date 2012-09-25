<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

/**
 * Page resource (page:://self/path/to/resource)
 *
 * @package BEAR.Resource
 *
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
