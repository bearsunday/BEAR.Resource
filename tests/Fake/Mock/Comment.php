<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Mock;

use BEAR\Resource\Annotation\Provides;
use BEAR\Resource\ResourceObject;

class Comment extends ResourceObject
{
    public function onGet(int $id)
    {
        return "entry {$id}";
    }

    /**
     * @Provides
     */
    public function provideId()
    {
        return ['aaa' => 1];
    }
}
