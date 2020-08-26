<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeCamelCaseParamResource extends ResourceObject
{
    public function onGet(string $userId, string $userRole = '')
    {
        $this->body = [
            'userId' => $userId,
            'userRole' => $userRole
        ];

        return $this;
    }
}
