<?php

namespace restbucks\Resource\App;

use BEAR\Resource\ObjectInterface as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource,
    BEAR\Resource\Uri;
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

    /**
     * @param Resource $resource
     */
    public function __construct()
    {
        $this->menu = ['coffee' => 300, 'latte' => 400];
    }

    /**
     * Menu
     *
     * @param string $drink
     *
     * @Link(rel="order", href="app://self/Order?drink={dring}")
     */
    public function onGet($drink = null)
    {
        if ($drink === null) {
            $this->body = $this->menu;

            return $this;
        }
        $this->links['order'] = new Uri('app://self/Order', ['drink' => $drink]);
        $this->body['drink'] = $drink;
        $this->body['price'] = $this->menu[$drink];

        return $this;
    }
}
