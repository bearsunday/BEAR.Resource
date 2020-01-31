<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\User;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Input\User as UserInput;

class Search extends ResourceObject
{
    private $repo = [
        ['id' => 3, 'name' => 'Hanako', 'country' => 'Japan', 'post_count' => 30, 'activated' => 0],
        ['id' => 1, 'name' => 'Yoko', 'country' => 'Japan', 'post_count' => 5, 'activated' => 1],
        ['id' => 2, 'name' => 'Taro', 'country' => 'Japan', 'post_count' => 25, 'activated' => 1],
        ['id' => 3, 'name' => 'John', 'country' => 'United States', 'post_count' => 0, 'activated' => 1]
    ];

    public function onGet(UserInput $condition)
    {
        $this->code = Code::NOT_FOUND;

        foreach ($this->repo as $user) {
            if ($user['activated'] !== (int) $condition->activated) {
                continue;
            }
            if ($condition->country !== null && $condition->country !== $user['country']) {
                continue;
            }
            if ($condition->postCount !== null && $condition->postCount >= $user['post_count']) {
                continue;
            }

            $this->code = Code::OK;
            $this->body = $user;
            break;
        }

        return $this;
    }
}
