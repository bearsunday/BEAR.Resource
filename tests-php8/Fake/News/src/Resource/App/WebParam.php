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
    public string $cookie;
    public string $env;
    public string $form;
    public string $query;
    public string $server;

    public function onGet(
        #[CookieParam('c')] string $cookie,
        #[EnvParam('e')] string $env,
        #[FormParam('f')] string $form,
        #[QueryParam('q')] string $query,
        #[ServerParam('s')] string $server): static
    {
        $this->cookie = $cookie;
        $this->env = $env;
        $this->form = $form;
        $this->query = $query;
        $this->server = $server;

        return $this;
    }
}
