Hypermedia framework for object as a service
--------------------------------------------

[![Latest Stable Version](https://poser.pugx.org/bear/resource/v/stable.png)](https://packagist.org/packages/bear/resource)
[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png?branch=master)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

**BEAR.Resource** はオブジェクトがリソースの振る舞いを持つHypermediaフレームワークです。
クライアントーサーバー、統一インターフェイス、ステートレス、相互接続したリソース表現、レイヤードコンポーネント等の
RESTのWebサービスの特徴をオブジェクトに持たせる事ができます。

既存のドメインモデルやアプリケーションの持つ情報を柔軟で長期運用を可能にするために、
アプリケーションをRESTセントリックなものにしAPI駆動開発を可能にします。

### リソースオブジェクト

リソースとして振る舞うオブジェクトがリソースオブジェクトです。

 * １つのURIのリソースが1クラスにマップされ、リソースクライアントを使ってリクエストします。
 * 統一されたリソースリクエストに対応したメソッドを持ち名前付き引き数でリクエストします。
 * メソッドはリクエストに応じてリソース状態を変更して自身`$this`を返します。


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
### インスタンスの取得

リソースクライアントはリソースオブジェクトのクライアントです。
インスタンスを取得するために[インスタンススクリプト](https://github.com/koriym/BEAR.Resource/blob/readme/scripts/instance.php)を`require`して
URIスキーマをクラスにマップし、リソースクライアントがリソースオブジェクトを`URI`で扱えるようにします。
```php
$resource = require '/path/to/BEAR.Resource/scripts/instance.php';
$resource->setSchemeCollection(
  (new SchemeCollection)
    ->scheme('app')
    ->host('self')
    ->toAdapter(new Adapter\App($injector, 'MyVendor\Sandbox', 'Resource\App'));
);
```

またはインジェクターを使って依存解決を行いクライアントインスタンスを取得します。

```php
$injector = Injector::create([new ResourceModule('MyVendor\Sandbox')])
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
```

どちらの方法でも **MyVendor\Sandbox\Resource\App\User** クラスが **app://self/user** というURIにマップされたリソースを扱うリソースクライアントが準備できます。

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

 * このリクエストは[PSR0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)に準拠した **MyVendor\Sandbox\Resource\App\User** クラスの **onGet($id)** メソッドに1を渡します。
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

リソースは関連するリソースの [ハイパーリンク](http://en.wikipedia.org/wiki/Hyperlink)を持つ事ができます
**@Link**アノテーションをメソッドにアノテートしてハイパーリンクを表します。

```php

use BEAR\Resource\Annotation\Link;

/**
 * @Link(rel="blog", href="app://self/blog?author_id={id}")
 */
```

**rel** でリレーション名を **href** (hyper reference)でリンク先URIを指定します。
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

### クロール

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

### HATEOAS アプリケーション状態のエンジンとしてのハイパーメディア

リソースはクライアントの次の動作をハイパーリンクにして、クライアントはそのリンクを辿りアプリケーションの状態を変更します。
例えば注文リソースに **POST** して注文を作成、その注文の状態から支払リソースに **PUT**して支払を行います。

Order リソース
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
HATEOAS について詳しくは[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)をご覧ください。

### リソース表現

リソースはそれぞれ表現のためのレンダラーを自身に持っています。
このレンダラーはリソースの依存なので、インジェクターを使ってレンダラーをインジェクトして利用します。


```php
$modules = [new ResourceModule('MyVendor\Sandbox'), new JsonModule]:
$resource = Injector::create(modules)
  ->getInstance('BEAR\Resource\ResourceInterface');
```

文字列評価されるとリソースはインジェクトされたリソースレンダラーを使ってリソース表現になります。

```php
echo $user;

// {
//     "name": "Aramis",
//     "age": 16,
//     "blog_id": 1
// }
```
このときの`$user`はレンダラーが内蔵された`ResourceObject`リソースオブジェクトです。
文字列ではないので配列やオブジェクトとしても取り扱うことができます。

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
リソース表現はAPI用の他にも、テンプレートエンジンを用いてHTMLにする事もできます。

## シグナルパラメーター

メソッドの実行には引き数が必要です。通常は以下の３つの優先順位でで引き数が用意されます。

  * メソッドを呼び出すコンシュマーが指定 ```$obj->method(1, 2, ...);```
  * メソッドシグネチャーでデフォルトを指定 ```function method($a1 = 1)```
  * メソッド内で`null`だったら内部で取得　```function method($cat = null) { $cat = $cat ?: new Cat;```

引き数の用意の責任をメソッドとコンシュマーから分離したのがシグナルパラメーターです。
コンシュマーとメソッドが引き数を用意しない場合のみ機能します。

シグナルパラメーターという名前は[シグナル・スロット](http://en.wikipedia.org/wiki/Signals_and_slots)というデザインパターンからのものです。
引き数が不足したときには変数名で`シグナル`が発信されて`スロット`として登録されているシグナルパラメーターがその不足を解決します。

### パラメータープロバイダーの登録

リソースクラインアントに変数名とプロバイダーの登録をします。

```php
$resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
$resource->attachParamProvider('user_id', new SessionIdParam);
```

この登録では`$user_id`という変数名の引き数が必要な時に`SessionIdParam`が呼ばれます。


### パラメータープロバイダーの実装

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

`SessionIdParam`は`ParamProviderInterface`インターフェイスを実装してパラメーター情報を受け取り、
**可能であれば**実引き数を用意して`$param->inject($args)`と返します。

パラメタープロバイダーは同一の変数名に複数登録でき、登録していたプロバイダーが次々に呼ばれます。
すべてのプロバイダーが実引き数を用意できないと`BEAR\Resource\Exception\Parameter`例外が投げられます。

### onProvidesメソッド

変数名を指定しないで`'*'`登録する`OnProvidesParam`はプロバイダーの用意が不要で、同一のクラスでの引き数のインジェクトを可能にします。

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
このリソースでクライアントが`$date`を指定しないと`onProvidesDate`が呼ばれ、返り値が`onPost`に渡されます。
`onPost`メソッド内では渡されたものだけを使うので、テスタビリティは向上し責任の分離したコードになります。

onProvidesメソッドの機能を利用するには`OnProvidesParam`パラメータープロバイダーを登録します。

```php
$resource->attachParamProvider('*', new OnProvidesParam);
```


## 埋め込みリソース

`@Embed`アノテーションを使って他のリソースを自身のリソースに埋め込む事が出来ます。`HTML`の`<img src="image_url">`や`<iframe src="content_url">`と同じ様に`src`で埋め込むリソースを指定します。

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

このNewsリソースでは`headline`と`sports`と同様に`weather`というリソースのリクエストを埋め込みます。

```
### HAL (Hypertext Application Language)

HAL Moduleを使うとリソース表現が[HAL](http://stateless.co/hal_specification.html)になります。リソースに埋め込まれたリクエストはHALでも埋め込みリソースとして評価されます。

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

結果
```javascript
{
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

### A Resource Oriented Framework

__BEAR.Sunday__ はリソース指向のフレームワークです。BEAR.Resourceに Webでの振る舞いやアプリケーションスタックの機能を、
Google GuiceスタイルのDI/AOPシステムの[Ray](https://github.com/koriym/Ray.Di)で追加してフルスタックのWebアプリケーションフレームワークとして機能します。
[BEAR.Sunday GitHub](https://github.com/koriym/BEAR.Sunday)をご覧下さい。
