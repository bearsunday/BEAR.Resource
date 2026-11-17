<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;
use BEAR\Resource\Fake\InputResourceBuiltinType;
use BEAR\Resource\Fake\InputResourceNoConstructor;
use BEAR\Resource\Fake\InputResourceWithDefaults;
use BEAR\Resource\Fake\InputResourceWithMixedParams;
use BEAR\Resource\Fake\InputResourceWithUntyped;
use BEAR\Resource\Fake\UserResource;
use FakeVendor\Sandbox\Resource\App\DocInvalidFile;
use FakeVendor\Sandbox\Resource\App\DocPhp7;
use FakeVendor\Sandbox\Resource\App\DocUser;
use FakeVendor\Sandbox\Resource\App\Link\HrefDeclaredFragment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/** @psalm-import-type Query from Types */
class OptionsTest extends TestCase
{
    protected Invoker $invoker;

    /** @var Query */
    protected array $query = [];
    protected Request $request;

    protected function setUp(): void
    {
        $schemaDir = __DIR__ . '/Fake/json_schema';
        $this->invoker = (new InvokerFactory())($schemaDir);
    }

    public function testOptionsMethod(): DocPhp7
    {
        $ro = new DocPhp7();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);

        return $ro;
    }

    #[Depends('testOptionsMethod')]
    public function testOptionsMethodBody(ResourceObject $ro): void
    {
        $actual = (string) $ro->view;
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
                "login_id": {
                    "type": "string",
                    "description": "Login ID"
                },
                "defaultNull": {
                    "type": "string",
                    "description": "DefaultNull"
                },
                "arr": {
                    "type": "array"
                },
                "time": {
                    "type": "string"
                }
            },
            "required": [
                "id",
                "name",
                "sw",
                "login_id",
                "arr",
                "time"
            ]
        }
    },
    "POST": {
        "request": {
            "parameters": {
                "id": {
                    "in": "server",
                    "type": "integer"
                },
                "a": {
                    "type": "string"
                }
            },
            "required": [
                "id",
                "a"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /** @return ResourceObject[][] */
    public static function roProvider(): array
    {
        return [
            [new FakeParamResource()],
            [new FakeParamResourceAttr()],
        ];
    }

    #[DataProvider('roProvider')]
    public function testAssistedResource(ResourceObject $ro): void
    {
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $this->assertSame('GET, POST, PUT, DELETE', $ro->headers['Allow']);
        $actual = (string) $ro->view;
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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithJsonSchema(): void
    {
        $ro = new DocUser();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsNoSchemaFile(): void
    {
        $ro = new DocInvalidFile();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
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
        $this->assertJsonStringEqualsJsonString($expected, (string) $ro->view);
    }

    public function testOptionsMethodWithInputAttribute(): void
    {
        $ro = new UserResource();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "name": {
                    "type": "string"
                },
                "email": {
                    "type": "string"
                }
            },
            "required": [
                "name",
                "email"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeWithDefaults(): void
    {
        $ro = new InputResourceWithDefaults();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "id": {
                    "type": "integer"
                },
                "name": {
                    "type": "string",
                    "default": "default"
                },
                "optional": {
                    "type": "string"
                }
            },
            "required": [
                "id"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeBuiltinType(): void
    {
        $ro = new InputResourceBuiltinType();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "name": {
                    "type": "string"
                }
            },
            "required": [
                "name"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeNoConstructor(): void
    {
        $ro = new InputResourceNoConstructor();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "input": {
                    "type": "BEAR\\\\Resource\\\\Fake\\\\InputNoConstructor"
                }
            },
            "required": [
                "input"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeMixedParams(): void
    {
        $ro = new InputResourceWithMixedParams();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "name": {
                    "type": "string"
                },
                "email": {
                    "type": "string"
                },
                "token": {
                    "type": "string"
                }
            },
            "required": [
                "name",
                "email",
                "token"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeUntyped(): void
    {
        $ro = new InputResourceWithUntyped();
        $request = new Request($this->invoker, $ro, Method::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'POST';
        $this->assertSame($actual, $expected);
        $actual = (string) $ro->view;
        $expected = '{
    "POST": {
        "request": {
            "parameters": {
                "name": {
                    "type": "string"
                },
                "untyped": []
            },
            "required": [
                "name",
                "untyped"
            ]
        }
    }
}
';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testOptionsMethodWithInputAttributeArrayDefault(): void
    {
        $actual = (new OptionsMethodRequest())(
            new ReflectionMethod(OptionsBranchResource::class, 'onPost'),
            [],
            [],
        );

        $this->assertSame(
            [
                'parameters' => [
                    'tags' => [
                        'type' => 'array',
                        'default' => '[]',
                    ],
                ],
            ],
            $actual,
        );
    }

    public function testOptionsMethodWithArrayDefault(): void
    {
        $actual = (new OptionsMethodRequest())(
            new ReflectionMethod(OptionsBranchResource::class, 'onGet'),
            [],
            [],
        );

        $this->assertSame(
            [
                'parameters' => [
                    'ids' => [
                        'type' => 'array',
                        'default' => '[]',
                    ],
                ],
            ],
            $actual,
        );
    }

    public function testALinkThatDeclaresAFragmentThrows(): void
    {
        $request = new Request($this->invoker, new HrefDeclaredFragment(), Method::OPTIONS);

        $this->expectException(UriException::class);
        $this->expectExceptionMessage('/author?id=1#top: a #[Link] href cannot have a fragment');

        $this->invoker->invoke($request);
    }
}
