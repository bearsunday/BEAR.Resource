# BEAR.Resource

## Hypermedia framework for object as a service

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Resource/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Resource)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Resource/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Resource)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml)


**BEAR.Resource** Is a Hypermedia framework that allows resources to behave as objects. It allows objects to have RESTful web service benefits such as client-server, uniform interface, statelessness, resource expression with mutual connectivity and layered components.

In order to introduce flexibility and longevity to your existing domain model or application data you can introduce an API as the driving force in your develpment by making your application REST-Centric in it's approach.

## Resource Object

The resource object is an object that has resource behavior.

 * 1 URI Resource is mapped to 1 class, it is retrieved by using a resource client.
 * A request is made to a method with named parameters that responds to a uniform resource request.
 * Through the request the method changes the resource state and return itself `$this`.


```php
<?php
namespace MyVendor\Sandbox\Blog;

class Author extends ResourceObject
{
    public $code = 200;

    public $headers = [
        'Content-Type' => 'application/json'
    ];

    public $body = [
        'id' =>1,
        'name' => 'koriym'
    ];

    #[Link(rel: 'blog', href: 'app://self/blog/post?author_id={id}')]
    public function onGet(int $id): static
    {
        return $this;
    }

    public function onPost(string $name): static
    {
        $this->code = 201; // created
        // ...
        return $this;
    }

    public function onPut(int $id, string $name): static
    {
        $this->code = 204; // no content
        //...
        return $this;
    }

    public function onDelete($id): static
    {
        $this->code = 204; // no content
        //...
        return $this;
    }
}
```
## Instance retrieval

You can retrieve a client instance by using an injector that resolves dependencies.

```php
use BEAR\Resource\ResourceInterface;

$resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
```

By either method the resource client that resolves a URI such as **app://self/user** to the mapped **Sandbox\Resource\App\User** can be provisioned.

## Resource request

Using the URI and a query the resource is requested.

```php
$user = $resource->get('app://self/user', ['id' => 1]);
```

 * This request passes 1 to the **onGet($id)** method in the **Sandbox\Resource\App\User** class that conforms to [PSR0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
 * The retrieved resource has 3 properties **code**, **headers** and **body**.

```php
var_dump($user->body);
```

```php
Array
(
 [name] => Athos
 [age] => 15
 [blog_id] => 0
)
```

There are two ways to request. One is `eager request` and the other is `lazy reuqust`.

### Eager Request

An eager request is a way to execute a request immediately. It is the same as normal PHP method execution. There are other different ways of writing with some reasons.

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->eager
  ->request();
```

```php
$user = $resource->get->uri('app://self/user')(['id' => 1]);
```

```php
$user = $resource->uri('app://self/user')(['id' => 1]); // 'get' request method can be omitted
```

### Lazy Request
 
 As known as [Lazy Loading](https://en.wikipedia.org/wiki/Lazy_loading), Request to resource until the point at which it is needed. One common use case is that assign to template `the resource request object` not the instance.

```php
// get `ResourceRequest` objcet 
$user = $resource->get->uri('app://self/user')->withQuery(['id' => 1]);
// assign to the template
echo "User resource body is {$user}"; // same in the template enigne template
```

It is callable object, you can invoke with other parameters;

```php
// invoke
$user1 = $user(); // $id = 1
$user2 = $user(['id' => 2);
```

## Hypermedia

A resource can contain [hyperlinks](http://en.wikipedia.org/wiki/Hyperlink) to other related resources.
Hyperlinks are declared with the **#[Link]** attribute on a method.

```php
use BEAR\Resource\Annotation\Link;

#[Link(rel: 'blog', href: 'app://self/blog?author_id={id}')]
```

The relation name is set by **rel** and link URI's are set by **href** (hyper reference).
The URI can assign the current resource value using the [URI Template](http://code.google.com/p/uri-templates/)([rfc6570](http://tools.ietf.org/html/rfc6570)).


Within a link their are several types **self**, **new**, **crawl** which can be used to effectively create a resource graph.

### linkSelf

`linkSelf` follows the link and returns the linked resource object.

```php
$blog = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkSelf('blog')
    ->eager
    ->request();
```
The result of the **app://self/user** resource request follows its **blog** link, and the request returns the **app://self/blog** resource object: its class, `uri`, `code`, headers, body and `view` are the blog resource's.
Just like clicking a link on a web page, the next resource takes the place of the current one.

In a chain such as `->linkSelf('blog')->linkSelf('author')` or `#link=self:blog,self:author`, the first rel is looked up in the `#[Link]`s of the requested method, and each later rel in those of the `onGet()` of the resource the previous self link returned; a following `linkNew` or `linkCrawl` adds to that resource. When the resource a self link returns has a code of 400 or more, the chain stops there and that resource is returned.

### linkNew

`linkNew` adds the linked resource to the response.

```php
$user = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkNew('blog')
    ->eager
    ->request();

$blog = $user['blog'];
```
In a web page this is like 'opening a page in a new window', while passing the current resource but also retreiving the next.

### Crawl

A crawl passes over a list of resources (array) in order retrieving their links, with this you can construct a more complictated resource graph. Just as a crawler crawls a web page, the resource client crawls hyperlinks and creates a resource graph.

Let's think of author, post, meta, tag, tag/name and they are all connected together by a resource graph.
Each resource has a hyperlink. Name this resource graph **post-tree** and give each `#[Link]` the crawl name and its *href*.

In the author resource there is a hyperlink to the post resource. This is a 1:n relationship.

```php
#[Link(crawl: 'post-tree', rel: 'post', href: 'app://self/post?author_id={id}')]
public function onGet($id = null)
```
In the post resource there is a hyperlink to meta and tag resources. This is also a 1:n relationship.

```php
#[Link(crawl: 'post-tree', rel: 'meta', href: 'app://self/meta?post_id={id}')]
#[Link(crawl: 'post-tree', rel: 'tag', href: 'app://self/tag?post_id={id}')]
public function onGet($author_id)
{
```

There is a hyperlink in the tag resource with only an ID for the tag/name resource that corresponds to that ID. It is a 1:1 relationship.

```php
#[Link(crawl: 'post-tree', rel: 'tag_name', href: 'app://self/tag/name?tag_id={tag_id}')]
public function onGet($post_id)
```

Set the crawl name and make the request.

```php
$graph = $resource
  ->get
  ->uri('app://self/marshal/author')
  ->linkCrawl('post-tree')
  ->eager
  ->request();
```

The resource client follows the `#[Link]`s that have the crawl name, connects each linked resource under its **rel** name, and creates a resource graph.

```
var_export($graph->body);

array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ),
 ...
```

### Links in the URI

A request URI and an `#[Embed]` src can name the links to follow in a `#link=` fragment. For example, `#link=self:blog` follows the resource's `#[Link(rel: 'blog')]`.

Each link is `type:rel` (`self`, `new` or `crawl`) and can add a query as `type:rel?query`; several links are joined with commas and followed in order.

```php
$request = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkSelf('blog')
    ->linkCrawl('post-tree');
echo $request->toUri(); // app://self/user?id=0#link=self:blog,crawl:post-tree

$ro = $resource->get('app://self/user?id=0#link=self:blog,crawl:post-tree'); // the same request
```

A linked URI is the `#[Link]` href filled with values from the body.

```php
class Order extends ResourceObject
{
    #[Link(rel: 'payment', href: 'app://self/payment{?id}')]
    #[Link(rel: 'item', href: 'app://self/item{?id}')]
    public function onGet(int $id): static
    {
        $this->body = ['id' => $id, 'items' => [['id' => 7], ['id' => 8]]];

        return $this;
    }
}

$resource->get('app://self/order?id=1#link=self:payment');           // GET app://self/payment?id=1 (id from the body)
$resource->get('app://self/order?id=1#link=self:payment?card=1234'); // GET app://self/payment?id=1&card=1234 (card added)
$resource->get('app://self/order?id=1#link=self:item?id=8');         // GET app://self/item?id=8 (id replaced with 8)
```

Only top-level body values fill the href. To pick one item, pass it in a link query such as `self:item?id=8`.

- A link is followed with GET, whatever `method` its `#[Link]` declares.
- A rel uses only `A-Z a-z 0-9 _ -`.
- A link query is written as `'#link=self:search?' . http_build_query(['tags' => ['php', 'bear']])`. A crawl link cannot take a query.
- A `#[Link]` href cannot have a fragment (`#top`, `{#var}`).

### HATEOAS Hypermedia as the Engine of Application State

The resource client next then takes the next behavior as hyperlink and carrying on from that link changes the application state.
For example in an order resource by using **POST** the order is created, from that order state to the payment resource using a **PUT** method a payment is made.

Order resource
```php
#[Link(rel: 'payment', href: 'app://self/payment{?order_id, credit_card_number, expires, name, amount}', method: 'put')]
public function onPost($drink)
```

Client code

```php
    $order = $resource
        ->post
        ->uri('app://self/order')
        ->withQuery(['drink' => 'latte'])
        ->eager
        ->request();

    $payment = [
        'credit_card_number' => '123456789',
        'expires' => '07/07',
        'name' => 'Koriym',
        'amount' => '4.00'
    ];

    // Now use a hyperlink to pay
    $response = $resource->href('payment', $payment);

    echo $response->code; // 201
```

The payment method is provided by the order resource with the hyperlink.
There is no change in client code even though the relationship between the order and payment is changed,
You can checkout more on HATEOAS at [How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow).

### Link header

A link declared with the rel `prefetch`, `canonical`, `next`, `prev`, `first` or `last` is also output as a `Link` header when `onGet()` returns 200. A CDN edge can warm its cache with `prefetch`, and an API client can page with `next`.

```php
#[Link(rel: 'next', href: '/articles?page={next}')]
#[Link(rel: 'prefetch', href: '/article/{top_id}')]
public function onGet(int $page = 1): static
```

```
Link: </articles?page=2>; rel="next", </article/43>; rel="prefetch"
```

A link is output only when all its template variables are in the body and its href expands to a path starting with `/` or an `http(s)://` URL. Up to five `prefetch` links are output.

Bind parameters
You can bind method parameters to an “external value”. The external value might be a web context or any other resource state.

## Bind parameters

You can bind method parameters to an "external value". The external value might be a web context or any other resource state.

### Web context parameter

For instance, Instead you "pull" `$_GET` or any global the web context values, You can bind PHP super global values to method parameters.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(#[QueryParam] string $id) : ResourceObject
    {
      // $id = $_GET['id'];
```

The above example is a case where a key name and the parameter name are the same.
You can specify `key` and `param` values when they don't match.

```php?start_inline
use Ray\WebContextParam\Annotation\CookieParam;

class News extends ResourceObject
{
    #[CookieParam(key: 'id'] 
    public function foo(string $tokenId) : ResourceObject
    {
      // $tokenId = $_COOKIE['id'];
```

Full List

```php

use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('use_id')] string $userId,        // $_GET['use_id'];
        #[CookieParam('id')] string $tokenId,          // $_COOKIE['id'] or "0000" when unset;
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode'];
        #[FormParam('token')] string $token,           // $_POST['token'];
        #[ServerParam('SERVER_NAME') #server           // $_SERVER['SERVER_NAME'];
    ) : ResourceObject {
```

This `bind parameter` is also very useful for testing.

### Resource Parameter

We can bind the status of another resource to a parameter with the `#[ResourceParam]` attribute.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    #[ResourceParam(param: 'name', uri: 'app://self/login#nickname')]
    public function onGet(string $name): static
    {
```

In this example, the `nickname` key of the `app://self/login` body is bound to `$name`. The fragment names a body key, so `#link=` in a `#[ResourceParam]` URI throws `UriException`.

## Ray.InputQuery Integration

BEAR.Resource integrates with [Ray.InputQuery](https://github.com/ray-di/Ray.InputQuery) to provide type-safe input handling and file upload processing.

### Input Objects

Use `#[Input]` attribute to convert flat query data into structured, type-safe objects:

```php
use Ray\InputQuery\Attribute\Input;

class ArticleResource extends ResourceObject
{
    public function onPost(#[Input] ArticleInput $article): static
    {
        $this->body = [
            'title' => $article->title,
            'author' => $article->author->name
        ];
        return $this;
    }
}
```

### File Upload

Use `#[InputFile]` attribute for type-safe file upload processing. HTML forms map directly to PHP method parameters:

```html
<form method="post" enctype="multipart/form-data">
    <input type="file" name="image" accept="image/*" required>
    <input type="text" name="title" placeholder="Title">
    <button type="submit">Upload</button>
</form>
```

```php
use Ray\InputQuery\Attribute\InputFile;
use Koriym\FileUpload\FileUpload;
use Koriym\FileUpload\ErrorFileUpload;

class ImageUpload extends ResourceObject
{
    public function onPost(
        #[InputFile(
            maxSize: 1024 * 1024, // 1MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/svg+xml'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'svg']
        )]
        FileUpload|ErrorFileUpload $image,
        string $title = 'Default Title'
    ): static {
        if ($image instanceof ErrorFileUpload) {
            $this->code = 400;
            $this->body = ['error' => true, 'message' => $image->message];
            return $this;
        }

        // Move file to upload directory
        $uploadDir = '/var/www/uploads/';
        $filename = uniqid() . '_' . $image->name;
        $image->move($uploadDir . $filename);

        $this->body = [
            'success' => true,
            'filename' => $image->name,
            'savedAs' => $filename,
            'size' => $image->size,
            'type' => $image->type,
            'title' => $title
        ];
        return $this;
    }
}
```

#### Multiple File Upload

Array parameters support multiple file uploads:

```html
<input type="file" name="images[]" multiple accept="image/*">
```

```php
/**
 * @param array<FileUpload|ErrorFileUpload> $images
 */
public function onPost(
    #[InputFile(maxSize: 2 * 1024 * 1024)]
    array $images
): static {
    // Process multiple files...
}
```

#### Testing File Uploads

File upload functionality is easy to test with direct object creation:

```php
use Koriym\FileUpload\FileUpload;

public function testFileUpload(): void
{
    $fileUpload = FileUpload::fromFile(__DIR__ . '/fixtures/test.jpg');
    
    $result = $this->resource->post('app://self/image-upload', [
        'image' => $fileUpload,
        'title' => 'Test Image'
    ]);
    
    $this->assertSame(200, $result->code);
    $this->assertTrue($result->body['success']);
}
```

The `#[InputFile]` attribute enables direct mapping between HTML form elements and PHP method parameters, where code becomes specification and improves readability.


### Resource Representation

Each resource has a renderer for representation. This renderer is a dependency of the resource, so it is injected in using an injector.
Apart from `JsonModule`you can also use the `HalModule` which uses a [HAL (Hyper Application Laungage)](http://stateless.co/hal_specification.html) renderer.


```php
$modules = [new ResourceModule('MyVendor\Sandbox'), new JsonModule]:
$resource = Injector::create(modules)
  ->getInstance('BEAR\Resource\ResourceInterface');
```

When the resource is output as a string the injected resource renderer is used then displayed as the resource representation.

```php
echo $user;

// {
//     "name": "Aramis",
//     "age": 16,
//     "blog_id": 1
// }
```

In this case `$user` is the renderers internal `ResourceObject`.
This is not a string so is treated as either an array or an object.

```php

echo $user['name'];

// Aramis

echo $user->onGet(2);

// {
//     "name": "Yumi",
//     "age": 15,
//     "blog_id": 2
// }
```
### Lazy Loading

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->request();

$smarty->assign('user', $user);
```

In a non `eager` `request()` not the resource request result but a request object is retrieved.
When this is assigned to the template engine at the timing of the output of a resource request `{$user}` in the template the `resource request` and `resource rendering` is executed and is displayed as a string.

## JSON Schema validation

The `#[JsonSchema]` attribute validates a resource's request parameters and/or
response body against a JSON Schema file. Install `JsonSchemaModule` with the
schema and validation directories:

```php
$module->install(new JsonSchemaModule(
    __DIR__ . '/var/json_schema',    // response body schemas
    __DIR__ . '/var/json_validate',  // request parameter schemas
));
```

```php
class User extends ResourceObject
{
    #[JsonSchema(schema: 'user.json', params: 'user.get.json')]
    public function onGet(int $age, string $gender = 'male'): static
    {
        $this->body = ['name' => ['firstName' => 'mucha'], 'age' => $age];

        return $this;
    }
}
```

When validation fails, `JsonSchemaException` is thrown. The exception carries
the original flat-string message **and** a typed `JsonSchemaErrors` collection
so handlers can render field-keyed responses without re-running the validator:

```php
use BEAR\Resource\Exception\JsonSchemaException;

try {
    $ro->onGet(10); // age < minimum:20
} catch (JsonSchemaException $e) {
    $errors = $e->getErrors();          // JsonSchemaErrors (Countable, IteratorAggregate)
    $errors->hasErrors();               // true
    count($errors);                     // 1
    foreach ($errors as $error) {
        $error->property;               // 'age'
        $error->pointer;                // '/age'
        $error->message;                // 'Must have a minimum value of 20'
        $error->constraint->name;       // 'minimum'
        $error->constraint->params;     // ['minimum' => 20]
    }
    $errors->byProperty();              // ['age' => [JsonSchemaError, ...]]
}
```

### Custom error message templates

The template lives in the schema (ajv-errors style `errorMessage`), not the
PHP code. `JsonSchemaError::render()` interpolates `{key}` placeholders
against the error's data:

```json
// user.get.json
{
  "type": "object",
  "properties": {
    "age": {
      "type": "integer", "minimum": 20,
      "errorMessage": { "minimum": "年齢は{minimum}歳以上である必要があります" }
    }
  }
}
```

```php
$tpl = $schemaErrorMessages[$error->property][$error->constraint->name];
$error->render($tpl); // '年齢は20歳以上である必要があります'
```

Placeholders: `{property}`, `{pointer}`, `{message}`, plus every key in
`$error->constraint->params`. Unknown placeholders are left in place.

For a single concatenated string, use `$errors->format()`:

```php
$errors->format();                            // 'minimum is 20\nis required\n'
$errors->format("<li>{property}: {message}</li>\n");
// '<li>age: minimum is 20</li>\n<li>name: is required</li>\n'
```

`$errors->first()` returns the leading `JsonSchemaError` (or `null` when empty),
which is handy when you only want to render a single error.

When a schema declares a custom message via ajv-errors style `errorMessage`, the
mapper renders it into `$error->message` automatically and sets
`$error->isCustomMessage = true`. The validator's own message is preserved in
`$error->rawMessage` so handlers can fall back to stable text for logs or
debugging.

### Request vs response source

```php
try {
    $resource->get('app://self/user', ['id' => 'not-an-int']);
} catch (JsonSchemaRequestException $e) {   // request validation failed (4xx)
} catch (JsonSchemaResponseException $e) {  // response validation failed (5xx)
}
```

Both extend `JsonSchemaException` — catching the parent still matches both.

### Custom handlers

Bind your own `JsonSchemaExceptionHandlerInterface` /
`JsonSchemaRequestExceptionHandlerInterface` to format the validation failure
into a 422 response, structured JSON error body, etc. The handlers receive
the concrete subclass that matches their role (`JsonSchemaResponseException`
and `JsonSchemaRequestException`, respectively), so
`$e->getErrors()` is the single source of truth for the structured failure
data.

## Embedding resources

The `#[Embed]` attribute embeds another resource in this one. Like `<img src="image_url">` or `<iframe src="content_url">` in HTML, the embedded resource is specified by `src`.

```php
class News extends ResourceObject
{
    #[Embed(rel: 'weather', src: 'app://self/weather/today')]
    public function onGet(): static
    {
        $this->body = [
            'headline' => '...',
            'sports' => '...',
        ];
        
        return $this;
    }
}
```

`weather` resource ie embedded like as `headline` or `sports` in this `News` resource.

### Resource layout

A class attribute that extends `AbstractResourceLayout` declares the resource's layout in its `EMBED` constant: the parts at the top level of the body, such as a header and a footer. They are in the body before `onGet()` runs, as `#[Embed]` puts them, and each `src` is expanded with the `onGet()` arguments.

```php
use Attribute;
use BEAR\Resource\Annotation\AbstractResourceLayout;
use BEAR\Resource\ResourceObject;

#[Attribute(Attribute::TARGET_CLASS)]
final class ContentPage extends AbstractResourceLayout
{
    /** @var array<string, string> */
    public const EMBED = ['footer' => 'page://self/footer{?id}'];
}

#[ContentPage]
class Article extends ResourceObject
{
    public function onGet(string $id): static
    {
        return $this;
    }
}
```

The template prints the parts it finds in the body; it does not fetch them.

A resource has one layout; a GET request to a class with two layout attributes, or with a layout that does not declare its own `EMBED`, throws `ResourceLayoutException`. To share parts between layouts, compose the constants instead of stacking attributes:

```php
#[Attribute(Attribute::TARGET_CLASS)]
final class CollectionPage extends AbstractResourceLayout
{
    /** @var array<string, string> */
    public const EMBED = [...ContentPage::EMBED, 'search' => 'page://self/search{?q}'];
}
```

A rel that both the layout and a method `#[Embed]` declare throws `ResourceLayoutException`. A subclass does not inherit the layout, since PHP does not inherit class attributes.

### HAL (Hypertext Application Language)

`HAL Module` changes resource representation as [HAL](http://stateless.co/hal_specification.html).

Embedded resource evaluate when it is present.


```php
    // create resource client with HalModule
    $resource = (new Injector(new HalModule(new ResourceModule('FakeVendor\Sandbox'))))->getInstance(ResourceInterface::class);
    // request
    $news = $resource
        ->get
        ->uri('app://self/news')
        ->withQuery(['date' => 'today'])
        ->request();
    // output
    echo $news . PHP_EOL;
```

Result

```json
    "headline": "40th anniversary of Rubik's Cube invention.",
    "sports": "Pieter Weening wins Giro d'Italia.",
    "_links": {
        "self": {
            "href": "/api/news?date=today"
        }
    },
    "_embedded": {
        "weather": [
            {
                "today": "the weather of today is sunny",
                "_links": {
                    "self": {
                        "href": "/api/weather?date=today"
                    },
                    "tomorrow": {
                        "href": "/api/weather/tomorrow"
                    }
                }
            }
        ]
    }
}

```

 [A demo application code](https://github.com/koriym/BEAR.Resource/tree/develop/docs/sample/06.HAL) is available.

`HalLinker::link()` returns the href and title of a rel, the same values HAL `_links` carries for an array body, for a template to build `<a href>` from. A body `_links[rel]` comes first, even for a rel the method does not declare, then the `#[Link]`; a rel in neither throws `LinkException`. A variable missing from the body is left out as RFC 6570 does. The title is the body link's title when the body link is used, otherwise the `#[Link]` title, and `''` when there is none, where HAL leaves `title` out.

```php
// #[Link(rel: 'next', href: '/posts{?page}', title: 'Next page')] on onGet, body ['page' => 2]
$halLinker->link($ro, 'next'); // ['href' => '/posts?page=2', 'title' => 'Next page']; href "/posts" without page
```

`ResourceLinks` hands those links to a template by rel. It is read-only, and reading a rel the resource does not link throws `LinkException`.

```php
$link = new ResourceLinks($ro, $halLinker);
$link['next']; // ['href' => '/posts?page=2', 'title' => 'Next page']
```

```twig
<a href="{{ _link.next.href }}" title="{{ _link.next.title }}">Next</a>
```

`isset()` is true for every rel, so in Twig `_link.prev is defined` is true even for a rel the resource does not link. Reading the value of such a rel throws `LinkException`: `_link.prev.href`, `_link.prev ?? 'x'`, `_link.prev|default('x')` and `{% if _link.prev %}`. A `#[Link]` rel is always there; a link the body adds to `_links` only on some responses is tested on the body (`_ro.body._links.next is defined` in Twig), which sees only links the body adds and is false for a rel only `#[Link]` declares.

## Representation

Cast `ResourceObject` to string to get the resource view.

```php
$userView = (string) $resource->get('app://self/user?id=1');
echo $userView; // get JSON
```

You can change the view format (media type) by injecting renderer. Following exmaple illustrates how simple json representation renderer "injected" in the constructor. Normally it is injected by dependency injection library.

```php
class User extends ResourceObject
{
    public function __construct()
    {
        $this->setRenderer(new class implements RenderInterface{
            public function render(ResourceObject $ro)
            {
                $ro->headers['content-type'] = 'application/json';
                $ro->view = json_encode($ro->body);

                return $ro->view;
            }
        });
    }
}
```

## Transfer

REST stands for representational state "transfer". `transfer()` method in `ResourceObject` output resource view to the client.

```php
$user = $resource->get('app://self/user?id=1');
$user->transfer(new class implements TransferInterface {
	public function __invoke(ResourceObject $ro, array $server)
	{
	    foreach ($ro->headers as $label => $value) {
	        header("{$label}: {$value}", false);
	    }
	    http_response_code($ro->code);
	    echo $ro->view;
	}
);
```
The above is simple http resoponse transfer example. This would be changed in different enviroment such as "console", "stream" or "socket serevr".

## Performance boost

A resource client is serializable. It has huge performance boosts. Recommended in production use.

```php

use BEAR\Resource\ResourceInterface;

// save
$resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
$cachedResource = serialize($resource);

// load
$resource = unserialize($cachedResource);
$news = $resource->get('app://self/news');
```

## Installation

```javascript
composer require bear/resource
```

## A Resource Oriented Framework

__BEAR.Sunday__ is a Resource Oriented Framework. In BEAR.Sunday on top of the web behavior in BEAR.Resource also has the added Google guice style DI/AOP System [Ray](https://github.com/koriym/Ray.Di) and is a web application framework.

Please check out [BEAR.Sunday web site](http://bearsunday.github.io/).

## See Also

 * [BEAR.Async](https://github.com/bearsunday/BEAR.Async) - Async/coroutine integration for BEAR.Resource.
 * [BEAR.QueryRepository](https://github.com/bearsunday/BEAR.QueryRepository) - Segregates reads and writes into two separate repository.
 * [Ray.WebParamModule](https://github.com/ray-di/Ray.WebParamModule) - Binds the value(s) of a web context to method parameter.

## Testing BEAR.Resource

Here's how to install BEAR.Resource from source and run the unit tests and demos.

```
composer create-project bear/resource BEAR.Resource
cd BEAR.Resource
./vendor/bin/phpunit
php demo/run.php
```
