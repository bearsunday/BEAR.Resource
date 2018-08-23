<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Canary extends ResourceObject
{
    public $links = [
        'friend' => 'app://self/bird/friend'
    ];

    public $body = [
        'name' => 'chill kun'
    ];

    /**
     * @Link(rel="friend", href="app://self/bird/friend")
     */
    public function onGet()
    {
        return $this;
    }
}
