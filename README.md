Hypermedia framework for object as a service
--------------------------------------------

[![Latest Stable Version](https://poser.pugx.org/bear/resource/v/stable.png)](https://packagist.org/packages/bear/resource)
[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png?branch=master)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/badges/quality-score.png?s=fa3351a652dc4a425a3bbb32c71438ce2dbb62c1)](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/)
[![Code Coverage](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/badges/coverage.png?s=56c3b44894ab8c7287c19e47bb6d98571e0e3309)](https://scrutinizer-ci.com/g/koriym/BEAR.Resource/)

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
### Instance retreival

The resource client is the resource object client. In order to retrieve an instance `require` the [instance script](https://github.com/koriym/BEAR.Resource/blob/master/scripts/instance.php), map your class to a URI schema, then the resource client can access the object as a 'URI'.

```php
$resource = require '/path/to/BEAR.Resource/scripts/instance.php';
$resource->setSchemeCollection(
  (new SchemeCollection)
    ->scheme('app')
    ->host('self')
    ->toAdapter(new Adapter\App($injector, 'MyVendor\Sandbox', 'Resource\App'));
);
```

You can also retrieve a client instance by using an injector that resolves depenencies.

```php
$injector = Injector::create([new ResourceModule('MyVendor\Sandbox')])
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
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


## Signal Parameter

In order to execute a method parameters are needed. Normally the following parameters are available in priority order:

  * Use of a consumer that calls the method ```$obj->method(1, 2, ...);```
  * Use of default method signature ```function method($a1 = 1)```
  * When null is present in a method instantiate internally. ```function method($cat = null) { $cat = $cat ?: new Cat;```

In order to seperate the provision responsibility of parameters from the method and consumer we use the `signal parameter`.
This only fires when the consumer and method does not provision the needed parameters.

The name `signal parameter` comes from the [Signal and Slots](http://en.wikipedia.org/wiki/Signals_and_slots) design pattern.
When a parameter is not available a `signal` is dispatched in the variable name and missing value is resolved by a signal parameter that is registered as a `slot`.

### Registering a Parameter Provider

Assign the variable names and provider in the resource client.

```php
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
$resource->attachParamProvider('user_id', new SessionIdParam);
```

In this case the when the parameter that has the variable name `$user_id` is needed, `SessionIdParam` is called.

### Parameter Provider Implementation

```php
class SessionIdParam implements ParamProviderInterface
{
    /**
     * @param Param $param
     *
     * @return mixed
     */
    public function __invoke(Param $param)
    {
        if (isset($_SESSION['login_id'])) {
            // found !
            return $param->inject($_SESSION['login_id']);
        };
        // no idea, ask another provider...
    }
}
```

`SessionIdParam` implements the `ParamProviderInterface` interface and recieves parameter data, **when possible** it then prepares the actual parameters and returns them in `$param->inject($args)`.

The parameter provider can register multiple parameters with a matching variable name, the registered provider will then be called by each of them. When none of the providers can prepare all parameters then `BEAR\Resource\Exception\ParameterException` exception is thrown.

### The `onProvides` Method

By not setting a variable name and assigning `OnProvidesParam` to '*' then setting up a provided is not needed, it is possible to inject parameters into a class method following a single pattern.

```php
class Post
{
    public function onPost($date)
    {
        // $date is passed by the onProvidesDate method.
    }

    public function onProvidesDate()
    {
        return date(DATE_RFC822);
    }
}
```
In this resource when `$date` is not specified in the client `onProvidesDate` is called, the returned value is passed to the `onPost` method.
In the `onPost` method only the values passed to it are used, which has a clear separation of concerns and gives you a vast improvement in testability.

To use the `onProvides` method functionality simply register the `OnProvidesParam` parameter provider.

```php
$resource->attachParamProvider('*', new OnProvidesParam);
```

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

`HAL Module` changes resource representatin as [HAL](http://stateless.co/hal_specification.html).

Embedded resource evaluete when it is present.


```php
    // create resource client with HalModule
    $resource = Injector::create([new ResourceModule('MyVendor\MyApp'), new HalModule])->getInstance('BEAR\Resource\ResourceInterface');
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

 [A demo code](https://github.com/koriym/BEAR.Resource/tree/develop/docs/sample/06.HAL) is available. 

### Requirements
 * PHP 5.4+

### Installation

```javascript
{
    "require": {
        "bear/resource": "~0.11"
    }
}
```

A Resource Oriented Framework
-----------------------------

__BEAR.Sunday__ is a Resource Oriented Framework. In BEAR.Sunday on top of the web behavior in BEAR.Resource also has the added Google guice style DI/AOP System [Ray](https://github.com/koriym/Ray.Di) and is a full stack web application framework.

Please check out [BEAR.Sunday on GitHub](https://github.com/koriym/BEAR.Sunday).
