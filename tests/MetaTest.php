<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Doc;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    /**
     * @var Meta
     */
    private $meta;

    public function setUp()
    {
        parent::setUp();
        $this->meta = new Meta(Doc::class);
    }

    public function testUri()
    {
        $this->assertSame('app://self/doc', $this->meta->uri);
    }

    public function testAllow()
    {
        $this->assertSame(['get', 'post', 'delete'], $this->meta->options->allow);
    }

    public function testParams()
    {
        /** @var Params $params */
        $params = $this->meta->options->params[1];
        $this->assertSame('post', $params->method);
        $this->assertSame(['id'], $params->required);
        $this->assertSame(['name', 'age'], $params->optional);
    }
}
