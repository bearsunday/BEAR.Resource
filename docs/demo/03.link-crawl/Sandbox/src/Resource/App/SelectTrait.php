<?php

namespace Sandbox\Demo03\Resource\App;

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
