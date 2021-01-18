<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\ResourceObject;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

class AttrWebContext extends ResourceObject
{
    public function onGet($id, $name = 'koriym')
    {
    }

    /**
     * Forward compatible attribute
     */
    #[CookieParam(param: "cookie", key: "c")]
    #[EnvParam(param: "env", key: "e")]
    #[FormParam(param: "form", key: "f")]
    #[QueryParam(param: "query", key: "q")]
    #[ServerParam(param: "server", key: "s")]
    public function onPost(string $cookie, string $env, string $form, string $query, string $server)
    {
    }

    #[CookieParam('c', param: "cookie")]
    public function onPut(string $cookie)
    {
    }

    #[CookieParam('c', param: "cookie")]
    public function onDelete(string $a, string $cookie = 'default')
    {
    }
}
