<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Menu extends ResourceObject
{
    private array $menu = ['coffee' => 300, 'latte' => 400];

    public function __construct()
    {
    }

    /**
     * @Link(rel="order", href="app://self/restbucks/order?drink={drink}", method="")
     */
    #[Link(rel: "order", href: "app://self/restbucks/order?drink={drink}", method: "")]
    public function onGet(string $drink = null)
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
