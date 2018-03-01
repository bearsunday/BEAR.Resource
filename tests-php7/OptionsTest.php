<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\DocInvalidFile;
use FakeVendor\Sandbox\Resource\App\DocPhp7;
use FakeVendor\Sandbox\Resource\App\DocUser;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class OptionsTest extends TestCase
{
    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        $this->invoker = new Invoker(new NamedParameter(new ArrayCache, new AnnotationReader, new Injector), new OptionsRenderer(new OptionsMethods(new AnnotationReader, $_ENV['schema_dir'])));
    }

    public function testOptionsMethod()
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
    public function testOptionsMethodBody(ResourceObject $ro)
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

    public function testAssistedResource()
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
                    "in": "cookie"
                },
                "env": {
                    "in": "env"
                },
                "form": {
                    "in": "formData"
                },
                "query": {
                    "in": "query"
                },
                "server": {
                    "in": "server"
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
                    "in": "cookie"
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
                "a": [],
                "cookie": {
                    "in": "cookie",
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

    public function testOptionsMethodWithJsonSchema()
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

    public function testOptionsNoSchemaFile()
    {
        $ro = new DocInvalidFile;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $expected = '{
    "GET": {
        "request": {
            "parameters": {
                "id": []
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
