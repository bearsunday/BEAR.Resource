<?php

namespace Space\Resource\App;

use BEAR\Resource\AbstractObject;

/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
class Index extends AbstractObject
{
    public function onGet()
    {
        return 'get';
    }

}