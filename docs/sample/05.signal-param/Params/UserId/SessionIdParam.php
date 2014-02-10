<?php

namespace Params\UserId;

use BEAR\Resource\Param;
use BEAR\Resource\ParamProviderInterface;

class SessionIdParam implements ParamProviderInterface
{
    /**
     * @param Param $param
     *
     * @return mixed
     */
    public function __invoke(Param $param)
    {
        // $id = $_SESSION['login_id'];
        $id = 1;

        return $param->inject($id);
    }
}

