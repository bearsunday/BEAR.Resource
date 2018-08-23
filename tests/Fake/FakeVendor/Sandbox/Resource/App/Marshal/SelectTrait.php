<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Marshal;

trait SelectTrait
{
    public function select($key, $id)
    {
        $result = [];
        foreach ($this->repo as $item) {
            if ($item[$key] == $id) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
