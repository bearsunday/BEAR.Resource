<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

class FakeParamResource extends ResourceObject
{
    public function onGet($id, $name = 'koriym')
    {
    }

    /**
     * @CookieParam(param="cookie", key="c")
     * @EnvParam(param="env", key="e")
     * @FormParam(param="form", key="f")
     * @QueryParam(param="query", key="q")
     * @ServerParam(param="server", key="s")
     *
     * @see attribute version at FakeParamResourceParam
     */
    public function onPost(string $cookie, string $env, string $form, string $query, string $server)
    {
    }

    /**
     * @CookieParam(param="cookie", key="c")
     */
    public function onPut(string $cookie)
    {
    }

    /**
     * @CookieParam(param="cookie", key="c")
     */
    public function onDelete(string $a, string $cookie = 'default')
    {
    }
}
