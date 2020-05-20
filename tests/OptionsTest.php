<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\DocInvalidFile;
use FakeVendor\Sandbox\Resource\App\DocPhp7;
use FakeVendor\Sandbox\Resource\App\DocUser;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var array<string, mixed>
     */
    protected $query = [];

    /**
     * @var Request
     */
    protected $request;

    protected function setUp() : void
    {
        $this->invoker = (new InvokerFactory)($_ENV['schema_dir']);
    }

    public function testOptionsMethod() : DocPhp7
    {
        $ro = new DocPhp7;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $response = $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);

        return $ro;
    }

    /**
     * @depends testOptionsMethod
     */
    public function testOptionsMethodBody(ResourceObject $ro) : void
    {
        $actual = $ro->view;
        $expected = '{
    "GET": {
        "request": {
            "parameters": {
                "id": {
                    "type": "integer",
                    "description": "Id"
                },
                "name": {
                    "type": "string",
                    "description": "Name"
                },
                "sw": {
                    "type": "bool",
                    "description": "Swithc"
                },
                "arr": {
                    "type": "array"
                },
                "defaultNull": {
                    "type": "string",
                    "description": "DefaultNull"
                }
            },
            "required": [
                "id",
                "name",
                "sw",
                "arr"
            ]
        }
    },
    "POST": {
        "request": {
            "parameters": {
                "id": {
                    "in": "server",
                    "type": "integer"
                }
            },
            "required": [
                "id"
            ]
        }
    }
}
';
        $this->assertSame($expected, $actual);
    }

    public function testAssistedResource() : void
    {
        $ro = new FakeParamResource;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $this->assertSame('GET, POST, PUT, DELETE', $ro->headers['Allow']);
        $actual = $ro->view;
        $expected = '{
    "GET": {
        "request": {
            "parameters": {
                "id": [],
                "name": {
                    "default": "koriym"
                }
            },
            "required": [
                "id"
            ]
        }
    },
    "POST": {
        "request": {
            "parameters": {
                "cookie": {
                    "in": "cookie",
                    "type": "string"
                },
                "env": {
                    "in": "env",
                    "type": "string"
                },
                "form": {
                    "in": "formData",
                    "type": "string"
                },
                "query": {
                    "in": "query",
                    "type": "string"
                },
                "server": {
                    "in": "server",
                    "type": "string"
                }
            },
            "required": [
                "cookie",
                "env",
                "form",
                "query",
                "server"
            ]
        }
    },
    "PUT": {
        "request": {
            "parameters": {
                "cookie": {
                    "in": "cookie",
                    "type": "string"
                }
            },
            "required": [
                "cookie"
            ]
        }
    },
    "DELETE": {
        "request": {
            "parameters": {
                "a": {
                    "type": "string"
                },
                "cookie": {
                    "in": "cookie",
                    "type": "string",
                    "default": "default"
                }
            },
            "required": [
                "a"
            ]
        }
    }
}
';
        $this->assertSame($expected, $actual);
    }

    public function testOptionsMethodWithJsonSchema() : void
    {
        $ro = new DocUser;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET';
        $this->assertSame($actual, $expected);
        $actual = $ro->view;
        $expected = '{
    "GET": {
        "summary": "User",
        "description": "Returns a variety of information about the user specified by the required $id parameter",
        "request": {
            "parameters": {
                "id": {
                    "type": "string",
                    "description": "User ID"
                }
            },
            "required": [
                "id"
            ]
        },
        "schema": {
            "type": "object",
            "properties": {
                "name": {
                    "$ref": "name.json#/definitions/name"
                },
                "age": {
                    "description": "Age in years",
                    "type": "integer",
                    "minimum": 20
                }
            },
            "required": [
                "name",
                "age"
            ]
        }
    }
}
';
        $this->assertSame($expected, $actual);
    }

    public function testOptionsNoSchemaFile() : void
    {
        $ro = new DocInvalidFile;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $expected = '{
    "GET": {
        "request": {
            "parameters": {
                "id": {
                    "type": "integer"
                }
            },
            "required": [
                "id"
            ]
        }
    }
}
';
        $this->assertSame($expected, $ro->view);
    }
}
