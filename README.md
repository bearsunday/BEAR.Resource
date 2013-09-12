Hypermedia framework for PHP
============================

[![Latest Stable Version](https://poser.pugx.org/bear/resource/v/stable.png)](https://packagist.org/packages/bear/resource)
[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

**BEAR.Resource** はオブジェクトにリソースの振る舞いを持たす事のできるHypermediaフレームワークです。
クライアントーサーバー、統一インターフェイス、ステートレス、相互接続したリソース表現、レイヤードコンポーネント等の
RESTのWebサービスの特徴をオブジェクトに持たす事ができます。

### リソースオブジェクト

リソースとして振る舞うオブジェクトがリソースオブジェクトです。

 * １つのURIのリソースがPHPの1クラスにマップされ、リソースクライアントを使ってリクエストします。
 * 統一されたリソースリクエストに対応したメソッドを持ち名前引き数でリクエストします。
 * メソッド内ではリクエストに応じてリソース状態を変更して自身を返します。


```php

namespace Sandbox\Blog;

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
### インスタンスの取得

リソースクライアントはリソースオブジェクトのクライアントです。
インスタンスを取得するために[インスタンススクリプト](https://github.com/koriym/BEAR.Resource/blob/readme/scripts/instance.php)を`require`して
URIスキーマをクラスにマップします。

```php
$resource = require '/path/to/BEAR.Resource/scripts/instance.php';
$resource->setSchemeCollection(
  (new SchemeCollection)
    ->scheme('app')
    ->host('self')
    ->toAdapter(new Adapter\App($injector, 'Sandbox', 'Resource\App'));
);
```

またはインジェクターを使って依存解決を行いクライアントインスタンスを取得します。

```php
$injector = Injector::create([new ResourceModule('Sandbox')])
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
```

どちらの方法でも **Sandbox\Resource\App\User** クラスが **app://self/user** というURIにマップされたリソースを扱う
リソースクライアントが準備できます。

### リソースリクエスト

URIとクエリーを使ってリソースをリクエストします。

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->eager
  ->request();
```

 * このリクエストは[PSR0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)に準拠した *Sandbox\Resource\App\User* クラスの **onGet($id)** メソッドに1を渡します。
 * 得られたリソースは **code**, **headers** それに **body**の３つのプロパティを持ちます。

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

リソースは関連するリソースの [ハイパーリンク](http://en.wikipedia.org/wiki/Hyperlink)を持つ事ができます。 **@Link**アノテーションをメソッドにアノテートします。

```php

use BEAR\Resource\Annotation\Link;

/**
 * @Link(rel="blog", href="app://self/blog?author_id={id}")
 */
```

**rel** で **リレーション名** を href (hyper reference)でリンク先URIを指定します。
URIは [URIテンプレート](http://code.google.com/p/uri-templates/)([rfc6570](http://tools.ietf.org/html/rfc6570))を用いて現在のリソースの値をアサインすることができます。

リンクには **self**, **new**, **crawl** といくつか種類があり効果的にリソースグラフを作成することができます。

### selfリンク

`linkSelf`はリンク先のリソースを取得します。

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
Webページでリンクをクリックしたように次のリソースに入れ替わります。

### newリンク

`linkNew` はリンク先のリソースも追加取得します。

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

Webページで「新しいウインドウでリンクを表示」を行うように現在のリソースは保持したまま次のリソースを取得します。

### crawl

クロールはリスト（配列）になっているリソースを順番にリンクを辿り、複雑なリソースグラフを構成することができます。
クローラーがwebページをクロールするように、リソースクライアントはハイパーリンクをクロールしソースグラフを生成します。

author, post, meta, tag, tag/name がそれぞれ関連づけられてあるリソースグラフを考えてみます。
それぞれのリソースはハイパーリンクを持ちます。
このリソースグラフに **post-tree** という名前を付け、それぞれのリソースの@Linkアノテーションでハイパーリファレンス **href** を指定します。

authorリソースにはpostリソースへのハイパーリンクがあります。1:nの関係です。
```php
/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
```

postリソースにはmetaリソースとtagリソースのハイパーリンクがあります。1:nの関係です。
```php
/**
 * @Link(crawl="post-tree", rel="meta", href="app://self/meta?post_id={id}")
 * @Link(crawl="post-tree", rel="tag",  href="app://self/tag?post_id={id}")
 */
public function onGet($author_id)
{
```

tagリソースはIDだけでそのIDに対応するtag/nameリソースへのハイパーリンクがあります。1:1の関係です。

```php
/**
 * @Link(crawl="post-tree", rel="tag_name",  href="app://self/tag/name?tag_id={tag_id}")
 */
public function onGet($post_id)
```

クロール名を指定してリクエストします。

```php
$graph = $resource
  ->get
  ->uri('app://self/marshal/author')
  ->linkCrawl('post-tree')
  ->eager
  ->request();
```

リソースクライアントは@Linkアノテーションに指定されたクロール名を発見するとその **rel** 名でリソースを接続してリソースグラフを作成します。

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

### HETEOAS アプリケーション状態のエンジンとしてのハイパーメディア

リソースはクライアントの次の動作をハイパーリンクにして、クライアントはそのリンクを辿りアプリケーションの状態を変更します。
例えば注文リソースに **POST** して注文を作成、その注文の状態から支払リソースに **PUT**して支払を行います。

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

支払の方法は注文リソースがハイパーリンクと提供しています。
支払と注文の関係が変更されてもクライアントコードに変更はありません。
HETEOAS について詳しくは[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)をご覧ください。

### リソース表現

リソースはそれぞれ表現のためのレンダラーを自身に持っています。
このレンダラーはリソースの依存なので、インジェクターを使ってレンダラーをインジェクトして利用します。
`JsonModule`の他にも[HAL (Hyper Application Laungage)](http://stateless.co/hal_specification.html)レンダラーを使う`HalModule` を利用することもできます。


```php
$modules = [new ResourceModule('Sandbox'), new JsonModule]:
$resource = Injector::create(modules)
  ->getInstance('BEAR\Resource\ResourceInterface');
```

文字列評価されるとリソースはインジェクトされたリソースレンダラーを使ってリソース表現になります。

```php

echo $user;

//{
//    "name": "Aramis",
//    "age": 16,
//    "blog_id": 1
//}
```

### 遅延評価

```php
$user = $resource
  ->get
  ->uri('app://self/user')
  ->withQuery(['id' => 1])
  ->request();

$smarty->assign('user', $user);
```

`eager`のない`request()`ではリソースリクエストの結果ではなく、リクエストオブジェクトが取得できます。
テンプレートエンジンにアサインするとテンプレートにリソースリクエスト`{$user}`が現れたタイミングで`リソースリクエスト`と`リソースレンダリング`を行い文字列表現になります。

※ リソース表現はAPI用の他にも、テンプレートエンジンを用いてHTMLにしたりもできます。

### クリーンコーディング

リソースはRay.Diインジェクターでインジェクションとアスペクトの織り込みが行われます。
関心の分離したクリーンなオブジェクトでリソースを構成できます。

```php
/**
 * @Inject
 */
public function __consutruct(Dependency $dependency1)
{
  // ...
}

/**
 * @Log
 * @Cache
 * @Db
 */
public function onPost($id)
{
```


Installation
============

The recommended way to install BEAR.Resource is through [Composer](https://github.com/composer/composer).

```bash
# Install Composer
$ curl -sS https://getcomposer.org/installer | php

# Add BEAR.Resource as a dependency
$ php composer.phar require bear/resource:*
```

A Resource Oriented Framework
============
__BEAR.Sunday__ is a resource oriented framework using BEAR.Resource as well as Gooogle Guice clone DI/AOP system [Ray](https://github.com/koriym/Ray.Di).
See more at [BEAR.Sunday GitHub](https://github.com/koriym/BEAR.Sunday).
