BEAR.Resource
=============

RESTful objects framework
-------------------------

[![Latest Stable Version](https://poser.pugx.org/bear/resource/v/stable.png)](https://packagist.org/packages/bear/resource)
[![Build Status](https://secure.travis-c	i.org/koriym/BEAR.Resource.png)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

BEAR.Resourceはオブジェクトにリソースの振る舞いを可能にするRESTfulオブジェクトフレームワークです。
[HATEOAS (Hypermedia as the Engine of Application State)](http://en.wikipedia.org/wiki/HATEOAS)をサポートします。

 * Service Layer - _Defines an application's boundary with a layer of services that establishes a set of available operations and coordinates the application's response in each operation. (Martin Fowler - PoEAA)_
 * REST Web Services Characteristics - _Client-Server, Stateless, Cache, Uniform interface, Named resources, Interconnected resource representations, and Layered components._

### Resource Obuject

１つのURIを持つリソースはPHPの1クラスにマップされます。リソースオブジェクトはリクエストメソッドに対応したメソッドを持ち、クエリーを名前引き数で受け取ります。
メソッド内ではリクエストに応じてリソース状態を変更して自身を返します。

```php
namespace Sandbox\Resource;

class User extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    /**
     * @Link(rel="blog", href="app://self/link/blog?blog_id={blog_id}")
     */
    public function onGet($id)
    {
        $this['name'] = $this->users[$id]['name'];
        $this['age'] = $this->users[$id]['age'];
        $this['blog_id'] = $this->users[$id]['blog_id'];
        
        return $this;
    }
}
```
下記のように記述しても同じ値を返します。
```php
    public function onGet($id)
    {
        $this->body = $this->users[$id];
        return $this;
    }
```

```php
    public function onGet($id)
    {
        return $this->users[$id];
    }
```



### Create resource client

リソースクライアントはリソースオブジェクトのクライアントです。
インスタンスを取得するためにディペンデンシーインジェクターを使って依存解決を行いクライアントインスタンスをを取得します。

```php
$injector = Injector::create([new ResourceModule]);
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
$scheme = (new SchemeCollection)
  ->scheme('app')
  ->host('self')
  ->toAdapter(new App($injector, 'Sandbox', 'Resource'));
$resource->setSchemeCollection($scheme);

```
これで **Sandbox\Resource\User** クラスは **app://self/user** というURIにマップされます。

### Request resource

URIと変数名を指定した名前付引き数を使ってリソースをリクエストします。

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->eager
  ->request();
```
このリクエストは **onGet($id)** メソッドに1を渡します。

得られたリソースは **code**, **headers** それに **body**の３つのプロパティを持ちます。

```php
var_dump($user->body);

// Array
// (
//  [name] => Athos
//  [age] => 15
//  [blog_id] => 0
//)
```

### Resource link

リソースは関連するリソースをリンクで持つ事ができます。 **@Link**アノテーションをメソッドにアノテートします。

```php

use BEAR\Resource\Annotation\Link;

/**
 * @Link(rel="blog", href="app://self/blog?blog_id={blog_id}")
 */
```

#### self link

**self link** はリンク先のリソースを取得します。

```php
$blog = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkSelf('blog')
    ->eager
    ->request();
```
**app://self/user** リソースをリクエストした結果で **blog** リンクを辿り **app://self/blog**リソースを取得します。
Webページでリンクをクリックしたようなものです。次のページに進み表示が入れ替わります。

#### new link

**new link** はリンク先のリソースも追加取得します。

```php
list($user, $blog) = $resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 0])
    ->linkNew('blog')
    ->eager
    ->request();
```
Webページで「新しいウインドウでリンクを表示」を行ったようなものです。
前のリソースは保持されたまま、リンク名を辿ってリンク先のリソースも取得します。

### crawl

クロールはリスト（配列）になっているリソースを順番にリンクを辿り、複雑なグラフ（ツリー）を構成することができます。
クローラーがwebページをクロールするようにリンクを辿りリソースグラフを生成します。

authorリソース postリソースのリンクがあります
```php
/**
 * @Link(crawl="tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
```

postリソース metaリソースとtagリソースのリンクがあります
```php
/**
 * @Link(crawl="tree", rel="meta", href="app://self/meta?post_id={id}")
 * @Link(crawl="tree", rel="tag",  href="app://self/tag?post_id={id}")
 */
public function onGet($author_id)
{
```

tagリソース tag/nameリソースのリンクがあります
```php
/**
 * @Link(crawl="tree", rel="tag_name",  href="app://self/tag/name?tag_id={tag_id}")
 */
public function onGet($post_id)
```

author/post(meta, tag(tag/name)) このリソースグラフを取得するためにリソースルートを **tree** というクロール名を指定してリクエストします。

```php
$graph = $resource
  ->get
  ->uri('app://self/marshal/author')
  ->linkCrawl('tree')
  ->eager
  ->request();
```
クロール名を発見するとその **rel** 名でリソースを接続してリソースグラフを作成します。

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

## HETEOAS

リンクはリソースの状態移行に使う事ができます。
リソースはクライアントの次の動作をリンクとして定義し、クライアントはそのリンクを利用します。

orederリソース
```php
/**
 * @Link(rel="payment", href="app://self/payment{?order_id, credit_card_number, expires, name, amount}", method="put")
 */
public function onPost($drink)
```

クライアントコード
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

    // then use hyper link to pay
    $response = $resource->href('payment', $payment);
    
    echo $response->code; // 201
```

注文リソースをpostで作成してリンクを辿り支払リソースにリクエストを行っています。

支払の方法は注文リソースからサービスされていて、支払と注文の関係が変わってもクライアントコードに変更はありません。詳しくは[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)をご覧ください。

### リソース表現

それぞれのリソースオブジェクトにはリソースを表現するためのリソースレンダラーを持っています。
(string)評価されるとそのそのレンダラーがリソースを描画します。デフォルトではJSONでレンダリングされます。

```php

echo $user;

//{
//    "name": "Aramis",
//    "age": 16,
//    "blog_id": 1
//}
```

リソース表現を他のものに（例えばテンプレートエンジンを用いてHTMLに）するには ***BEAR\Resource\RenderInterface***インターフェイスにレンダラーを束縛します。

```php
 $this->bind('BEAR\Resource\RenderInterface')->to('\your\resource\renderer\class');
```
### Lazy evaluation

リソースリクエストの結果ではなくリソースリクエストをテンプレートにアサインすると評価を遅延することができます。

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->request();

$smarty->assign('user', $user);
```
テンプレートに{$user}が現れたら、リソースリクエストとリソースレンダリングを行いリソースの文字列表現になります。


Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Aop with it.

	{
		"require": {
			"bear/resource": ">=0.1"
		}
	}

A Resource Oriented Framework
============
__BEAR.Sunday__ is a resource oriented framework using BEAR.Resource as well as Gooogle Guice clone DI/AOP system [Ray](https://github.com/koriym/Ray.Di).
See more at [BEAR.Sunday GitHub](https://github.com/koriym/BEAR.Sunday).
