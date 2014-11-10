<?php

namespace TestVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class Sparrows extends ResourceObject
{
    /**
     * @Embed(rel="birdRequest", src="app://self/bird/sparrow")
     * @Embed(rel="birdObject", src="app://self/bird/sparrow")
     * @Embed(rel="eagerRequestedBird", src="app://self/bird/sparrow")
     */
    public function onGet($id_request, $id_object, $id_eager_request)
    {
        $this['birdRequest'] = $this['birdRequest']->addQuery(['id' => $id_request]);
        $this['birdObject'] = $this['birdObject']->addQuery(['id' => $id_object])->eager->request();
        $this['eagerRequestedBird'] = $this['eagerRequestedBird']->addQuery(['id' => $id_eager_request])->eager;

        return $this;
    }
}
