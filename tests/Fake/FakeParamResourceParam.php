<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

class FakeParamResourceParam extends ResourceObject
{
    public function onGet($id, $name = 'koriym')
    {
    }

    public function onPost(
        #[CookieParam(key: 'c')] string $cookie,
        #[EnvParam(key: 'e')] string $env,
        #[FormParam(key: 'f')] string $form,
        #[QueryParam(key: 'q')] string $query,
        #[ServerParam(key: 's')] string $server
    ){
    }

    public function onPut(#[CookieParam(key: 'c')] string $cookie)
    {
    }

    public function onDelete(string $a, #[CookieParam(key: 'c')] string $cookie = 'default')
    {
    }
}
