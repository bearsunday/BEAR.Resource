<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Sandbox\Resource\App;

trait SelectTrait
{
    public function select($key, $id)
    {
        $result = [];
        foreach ($this->repo as $item) {
            if ($item[$key] == $id) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
