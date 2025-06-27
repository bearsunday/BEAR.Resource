<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class ClassValueObject extends ResourceObject
{
    public function onGet(
        PersonWithService $person,
        PersonWithQualifiedService $qualifiedPerson,
        PersonWithNamedService $namedPerson,
        ServiceInterface $service,
        string $defaultValue = 'default value'
    ){
        $this->body = [
            'person' => [
                'name' => $person->name,
                'age' => $person->age,
                'service' => $person->service->serve(),
            ],
            'qualifiedPerson' => [
                'name' => $qualifiedPerson->name,
                'age' => $qualifiedPerson->age,
                'service' => $qualifiedPerson->service->serve(),
            ],
            'namedPerson' => [
                'name' => $namedPerson->name,
                'age' => $namedPerson->age,
                'service' => $namedPerson->service->serve(),
            ],
            'service' => $service->serve(),
            'defaultValue' => $defaultValue,
        ];

        return $this;
    }
}
