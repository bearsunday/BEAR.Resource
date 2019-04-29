<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
