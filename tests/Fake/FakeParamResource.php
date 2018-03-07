<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
     */
    public function onPost($cookie, $env, $form, $query, $server)
    {
    }

    /**
     * @CookieParam(param="cookie", key="c")
     */
    public function onPut($cookie)
    {
    }

    /**
     * @CookieParam(param="cookie", key="c")
     */
    public function onDelete($a, $cookie = 'default')
    {
    }
}
