<?php
/**
 * Created by JetBrains PhpStorm.
 * User: akihito
 * Date: 2013/09/07
 * Time: 12:11
 * To change this template use File | Settings | File Templates.
 */

namespace Sandbox\Resource\App\Link;


trait SelectTrait {

    public function select($key, $id)
    {
        $result = [];
        foreach($this->repo as $item) {
            $a = ($item[$key] );
            if ($item[$key] == $id) {
                $result[] = $item;
            }
        }
        return $result;
    }
}