<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Author;

class ResourceObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testTransfer()
    {
        $resourceObject = new FakeResource;
        $responder = new FakeResponder;
        $resourceObject->transfer($responder, []);
        $this->assertSame(FakeResource::class, $responder->class);
    }

    public function testSerialize()
    {
        $ro = new FakeFreeze;
        $ro->uri = new Uri('app://self/freeze');
        $serialized = serialize($ro);
        $this->assertInternalType('string', $serialized);
        $expected = 'O:24:"BEAR\Resource\FakeFreeze":5:{s:3:"uri";O:17:"BEAR\Resource\Uri":5:{s:6:"scheme";s:3:"app";s:4:"host";s:4:"self";s:4:"path";s:7:"/freeze";s:5:"query";a:0:{}s:6:"method";N;}s:4:"code";i:201;s:7:"headers";a:0:{}s:4:"body";a:2:{s:3:"php";s:1:"7";s:4:"user";O:38:"FakeVendor\Sandbox\Resource\App\Author":5:{s:3:"uri";O:17:"BEAR\Resource\Uri":5:{s:6:"scheme";s:3:"app";s:4:"host";s:4:"self";s:4:"path";s:7:"/author";s:5:"query";a:1:{s:2:"id";i:1;}s:6:"method";s:3:"get";}s:4:"code";i:200;s:7:"headers";a:0:{}s:4:"body";a:3:{s:4:"name";s:6:"Aramis";s:3:"age";i:16;s:7:"blog_id";i:12;}s:4:"view";N;}}s:4:"view";N;}';
        $this->assertSame($expected, $serialized);
        $resourceObject = unserialize($serialized);
        $this->assertInstanceOf(Author::class, $resourceObject['user']);
        $expected = 'app://self/freeze';
        $this->assertSame($expected, (string) $resourceObject->uri);
    }

    public function testJson()
    {
        $ro = new FakeFreeze;
        $ro->uri = new Uri('app://self/freeze');
        $json = json_encode($ro);
        $this->assertInternalType('string', $json);
        $expected = '{"php":"7","user":{"name":"Aramis","age":16,"blog_id":12}}';
        $this->assertSame($expected, $json);
    }
}
