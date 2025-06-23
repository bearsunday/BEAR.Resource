<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Annotation\Scalar;
use BEAR\Resource\ResourceObject;

class ScalarValueObject extends ResourceObject
{
    public function onGet(
        #[Scalar] IntValue $int,
        #[Scalar] StringValue $string,
        #[Scalar] BoolValue $bool,
        #[Scalar] FooValue $foo,
        #[Scalar] QualifiedIntValue $qualifiedInt,
        #[Scalar] NamedIntValue $namedInt
    ){
        $this->body = [
            'int' => $int->value,
            'string' => $string->value,
            'bool' => $bool->value,
            'foo' => $foo->value,
            'qualifiedInt' => $qualifiedInt->value,
            'service' => $int->service->serve(),
            'qualifiedService' => $qualifiedInt->service->serve(),
        ];

        return $this;
    }
}
