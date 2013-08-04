<?php

namespace Sandbox\Resource\App\RestBucks;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Uri;
use BEAR\Resource\Annotation\Link;
use Ray\Di\Di\Scope;

/**
 * Order
 *
 * @Scope("singleton")
 */
class Menu extends AbstractObject
{
    private $menu = [];

    public function __construct()
    {
        $this->menu = array('coffee' => 300, 'latte' => 400);
    }

    /**
     * Menu
     *
     * @param string $drink
     *
     * @return \Sandbox\Resource\App\RestBucks\Menu
     *
     * @Link(rel="order", href="app://self/RestBucks/Order?drink={drink}")
     */
    public function onGet($drink = null)
    {
        if ($drink === null) {
            $this->body = $this->menu;

            return $this;
        }
        $this->links['order'] = new Uri('app://self/RestBucks/Order', array('drink' => $drink));
        $this->body['drink'] = $drink;
        $this->body['price'] = $this->menu[$drink];

        return $this;
    }
}
