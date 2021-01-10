<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\ResourceObject;

use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

class WebParam extends ResourceObject
{
    public $cookie;
    public $env;
    public $form;
    public $query;
    public $server;

    public function onGet(
        #[CookieParam(key: 'c')] string $cookie,
        #[EnvParam(key: 'e')] string $env,
        #[FormParam(key: 'f')] string $form,
        #[QueryParam(key: 'q')] string $query,
        #[ServerParam(key: 's')] string $server)
    {
        $this->cookie = $cookie;
        $this->env = $env;
        $this->form = $form;
        $this->query = $query;
        $this->server = $server;
    }
}
