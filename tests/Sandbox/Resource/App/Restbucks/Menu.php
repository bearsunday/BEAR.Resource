<?php

namespace Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use BEAR\Resource\Annotation\Link;
use Ray\Di\Di\Scope;

class Menu extends ResourceObject
{
    private $menu = [];

    public function __construct()
    {
        $this->menu = ['coffee' => 300, 'latte' => 400];
    }

    /**
     * Menu
     *
     * @param string $drink
     *
     * @return \Sandbox\Resource\App\RestBucks\Menu
     *
     * @Link(rel="order", href="app://self/restbucks/order?drink={drink}", method="")
     */
    public function onGet($drink = null)
    {
        if ($drink === null) {
            $this->body = $this->menu;

            return $this;
        }
        $this['drink'] = $drink;
        $this['price'] = $this->menu[$drink];

        return $this;
    }
}
