<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class Sparrows extends ResourceObject
{
    /**
     * @Embed(rel="birdRequest", src="app://self/bird/sparrow")
     * @Embed(rel="birdObject", src="app://self/bird/sparrow")
     * @Embed(rel="eagerRequestedBird", src="app://self/bird/sparrow")
     */
    public function onGet(int $id_request, int $id_object, int $id_eager_request)
    {
        $this['birdRequest'] = $this['birdRequest']->addQuery(['id' => $id_request]);
        $this['birdObject'] = $this['birdObject']->addQuery(['id' => $id_object])->eager->request();
        $this['eagerRequestedBird'] = $this['eagerRequestedBird']->addQuery(['id' => $id_eager_request])->eager;

        return $this;
    }
}
