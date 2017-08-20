# BEAR.Resource

## Hypermedia framework for object as a service

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Resource.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Resource)

**BEAR.Resource** Is a Hypermedia framework that allows resources to behave as objects. It allows objects to have RESTful web service benefits such as client-server, uniform interface, statelessness, resource expression with mutual connectivity and layered components.

In order to introduce flexibility and longevity to your existing domain model or application data you can introduce an API as the driving force in your develpment by making your application REST-Centric in it's approach.

### Resource Object

The resource object is an object that has resource behavior.

 * 1 URI Resource is mapped to 1 class, it is retrieved by using a resource client.
 * A request is made to a method with named parameters that responds to a uniform resource request.
 * Through the request the method changes the resource state and return itself `$this`.


```php

namespace MyVendor\Sandbox\Blog;

class Author extends ResourceObject
{
    public $code = 200;

    public $headers = [
    ];

    public $body = [
        'id' =>1,
        'name' => 'koriym'
    ];

    /**
     * @Link(rel="blog", href="app://self/blog/post?author_id={id}")
     */
    public function onGet($id)
    {
        return $this;
    }

    public function onPost($name)
    {
        $this->code = 201; // created
        // ...
        return $this;
    }

    public function onPut($id, $name)
    {
        //...
    }

    public function onDelete($id)
    {
        //...
    }
```
### Instance retrieval

You can retrieve a client instance by using an injector that resolves dependencies.

```php
use BEAR\Resource\ResourceInterface;

$resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
```

By either method the resource client that resolves a URI such as **app://self/user** to the mapped **Sandbox\Resource\App\User** can be provisioned.

### Resource request

Using the URI and a query the resource is requested.

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->eager
  ->request();
```

 * This request passes 1 to the **onGet($id)** method in the **Sandbox\Resource\App\User** class that conforms to [PSR0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
 * The retrieved resource has 3 properties **code**, **headers** and **body**.

```php
var_dump($user->body);

// Array
// (
//  [name] => Athos
//  [age] => 15
//  [blog_id] => 0
//)
```


## Hypermedia

A resource can contain [hyperlinks](http://en.wikipedia.org/wiki/Hyperlink) to other related resources.
Hyperlinks are shown by methods annotated with **@Link**.

```php

use BEAR\Resource\Annotation\Link;

/**
 * @Link(rel="blog", href="app://self/blog?author_id={id}")
 */
```

The relation name is set by **rel** and link URI's are set by **href** (hyper reference).
The URI can assign the current resource value using the [URI Template](http://code.google.com/p/uri-templates/)([rfc6570](http://tools.ietf.org/html/rfc6570)).


Within a link their are several types **self**, **new**, **crawl** which can be used to effectively create a resource graph.

### linkSelf

`linkSelf` retrieves the linked resource.

```php
$blog = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkSelf('blog')
    ->eager
    ->request();
```
The result of the  **app://self/user** resource request jumps over the the **blog** link and retrieves the **app://self/blog** resource.
Just like clicking a link a the webpage it is replaced by the next resource.

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
Each resource has a hyperlink. In ths resource graph add the name **post-tree**, on each resource add the hyper-reference *href* in the @link annotation.

In the author resource there is a hyperlink to the post resource. This is a 1:n relationship.
```php
/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
```
In the post resource there is a hyperlink to meta and tag resources. This is also a 1:n relationship.

```php
/**
 * @Link(crawl="post-tree", rel="meta", href="app://self/meta?post_id={id}")
 * @Link(crawl="post-tree", rel="tag",  href="app://self/tag?post_id={id}")
 */
public function onGet($author_id)
{
```

There is a hyperlink in the tag resource with only an ID for the tag/name resource that corresponds to that ID. It is a 1:1 relationship.

```php
/**
 * @Link(crawl="post-tree", rel="tag_name",  href="app://self/tag/name?tag_id={tag_id}")
 */
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

The resource client looks for the crawl name annotated with @link using the **rel** name connects to the resource and creates a resource graph.

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

### HATEOAS Hypermedia as the Engine of Application State

The resource client next then takes the next behavior as hyperlink and carrying on from that link changes the application state.
For example in an order resource by using **POST** the order is created, from that order state to the payment resource using a **PUT** method a payment is made.

Order resource
```php
/**
 * @Link(rel="payment", href="app://self/payment{?order_id, credit_card_number, expires, name, amount}", method="put")
 */
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

## Embedding resources

`@Embed` annotations makes easier to embed external resource to its own. Like `<img src="image_url">` or `<iframe src="content_url">` in HTML, Embedded resource is specified by `src` field.

```php
class News extends ResourceObject
{
    /**
     * @Embed(rel="weather",src="app://self/weather/today")
     */
    public function onGet()
    {
        $this['headline'] = "...";
        $this['sports'] = "...";
        
        return $this;
    }
}
```

`weather` resource ie embedded like as `headline` or `sports` in this `News` resource.

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

```javascript
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


## Performance boost ##

A resource client is serializable. It has huge performance boosts. Recommended in production use.

```php

use BEAR\Resource\ResourceInterface;

// save
$resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
$cachedResource = serialize($resource);

// load
$resource = unserialize($cachedResource);
$news = $resource->get->uri('app://self/news')->request();
```

## Installation

```javascript
composer require bear/resource ^1.0
```

## A Resource Oriented Framework

__BEAR.Sunday__ is a Resource Oriented Framework. In BEAR.Sunday on top of the web behavior in BEAR.Resource also has the added Google guice style DI/AOP System [Ray](https://github.com/koriym/Ray.Di) and is a web application framework.

Please check out [BEAR.Sunday web site](http://bearsunday.github.io/).

## See Also

 * [BEAR.QueryRepository](https://github.com/bearsunday/BEAR.QueryRepository) - Segregates reads and writes into two separate repository.
 * [Ray.WebParamModule](https://github.com/ray-di/Ray.WebParamModule) - Binds the value(s) of a web context to method parameter.

## Testing BEAR.Resource

Here's how to install BEAR.Resource from source and run the unit tests and demos.

```
composer create-project bear/resource BEAR.Resource
cd BEAR.Resource
phpunit
php docs/demo/run.php
```
