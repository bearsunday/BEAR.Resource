<?php
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

interface Resource
{
//     public function get(ResourceObject $ro, array $args);
    public function post(ResourceObject $ro, array $args);
//     public function update(ResourceObject $ro, array $args);
//     public function delete(ResourceObject $ro, array $args);
//     public function head(ResourceObject $ro, array $args);
//     public function newInstance($uri, array $args);
//     public function set($target);
//     public function in($mode);
}
