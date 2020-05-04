<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Exception\ServerErrorException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testBadRequestExceptionCode() : void
    {
        $this->assertSame((new BadRequestException)->getCode(), Code::BAD_REQUEST);
        $this->assertSame((new BadRequestException('', 1))->getCode(), 1);
    }

    public function testResourceNotFoundExceptionCode() : void
    {
        $this->assertSame((new ResourceNotFoundException)->getCode(), Code::NOT_FOUND);
        $this->assertSame((new ResourceNotFoundException('', 1))->getCode(), 1);
    }

    public function testServerErrorExceptionCode() : void
    {
        $this->assertSame((new ServerErrorException)->getCode(), Code::ERROR);
        $this->assertSame((new ServerErrorException('', 1))->getCode(), 1);
    }
}
