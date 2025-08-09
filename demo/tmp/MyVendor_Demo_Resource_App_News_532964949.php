<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class News_532964949 extends News implements \Ray\Aop\WeavedInterface  {
    use \Ray\Aop\InterceptTrait;
        #[\BEAR\Resource\Annotation\Embed(rel: 'weather', src: 'app://self/weather{?date}')]
      public function onGet(string $date)
    {
        return $this->_intercept(__FUNCTION__, func_get_args());
    }
}