<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

/**
 * Interface for Http resource adapter
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
interface HttpClient
{
    public function onGet();
    public function onPost();
    public function onPut();
    public function onDelete();
    public function onOptions();
    public function onHead();
}