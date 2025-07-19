---
layout: docs-ja
title: MediaQuery
category: Manual
permalink: /manuals/1.0/ja/database_media.html
---

# Ray.MediaQuery

`Ray.MediaQuery`はDBやWeb APIなどの外部メディアのクエリーのインターフェイスから、クエリー実行オブジェクトを生成しインジェクトします。

* ドメイン層とインフラ層の境界を明確にします。
* ボイラープレートコードを削減します。
* 外部メディアの実体には無関係なので、後からストレージを変更することができます。並列開発やスタブ作成が容易です。

## インストール

```bash
$ composer require ray/media-query
```

## 利用方法

メディアアクセスするインターフェイスを定義します。

### データベースの場合

`DbQuery`アトリビュートでSQLのIDを指定します。

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web APIの場合

`WebQuery`アトリビュートでWeb APIのIDを指定します。

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

APIパスリストのファイルを`media_query.json`として作成します。

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {
            "id": "user_item",
            "method": "GET",
            "path": "https://{domain}/users/{id}"
        }
    ]
}
```

MediaQueryModuleは、`DbQueryConfig`や`WebQueryConfig`、またはその両方の設定でSQLやWeb APIリクエストの実行をインターフェイスに束縛します。

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),
            [
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.exmaple.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password'
    ));
}
```

MediaQueryModuleはAuraSqlModuleのインストールが必要です。

### 注入

インターフェイスからオブジェクトが直接生成され、インジェクトされます。実装クラスのコーディングが不要です。

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### DbQuery

SQL実行がメソッドにマップされ、IDで指定されたSQLをメソッドの引数でバインドして実行します。例えばIDが`todo_item`の指定では`todo_item.sql`SQL文に`['id => $id]`をバインドして実行します。

* `$sqlDir`ディレクトリにSQLファイルを用意します。
* SQLファイルには複数のSQL文が記述できます。最後の行のSELECTが返り値になります。

#### Entity

SQL実行結果を用意したエンティティクラスを`entity`で指定して変換（hydrate）することができます。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
final class Todo
{
    public string $id;
    public string $title;
}
```

プロパティをキャメルケースに変換する場合には`CamelCaseTrait`を使います。

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;
    public $userName;
}
```

コンストラクタがあると、フェッチしたデータでコールされます。

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

SQL実行の戻り値が単一行なら`type: 'row'`のアトリビュートを指定します。ただし、インターフェイスの戻り値がエンティティクラスなら省略することができます。

```php
/** 返り値がEntityの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** 返り値がarrayの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

### Web API

* メソッドの引数が `uri`で指定されたURI templateにバインドされ、Web APIリクエストオブジェクトが生成されます。
* 認証のためのヘッダーなどのカスタムはGuzzleの`ClinetInterface`をバインドして行います。

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvider::class);
```

## パラメーター

### 日付時刻

パラメーターにバリューオブジェクトを渡すことができます。例えば、`DateTimeInterface`オブジェクトをこのように指定できます。

```php
interface TaskAddInterface
{
    #[DbQuery('task_add')]
    public function __invoke(string $title, DateTimeInterface $createdAt = null): void;
}
```

値はSQL実行時やWeb APIリクエスト時に日付フォーマットされた文字列に変換されます。

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

値を渡さないとバインドされている現在時刻がインジェクションされます。SQL内部で`NOW()`とハードコーディングする事や、毎回現在時刻を渡す手間を省きます。

### テスト時刻

テストの時には以下のように`DateTimeInterface`の束縛を1つの時刻にすることもできます。

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

### バリューオブジェクト（VO）

`DateTime`以外のバリューオブジェクトが渡されると`ToScalarInterface`を実装した`toScalar()`メソッド、もしくは`__toString()`メソッドの返り値が引数になります。

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user
    ) {}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### パラメーターインジェクション

バリューオブジェクトの引数のデフォルトの値の`null`がSQLやWebリクエストで使われることはないことに注意してください。値が渡されないと、nullの代わりにパラメーターの型でインジェクトされたバリューオブジェクトのスカラー値が使われます。

```php
public function __invoke(Uuid $uuid = null): void; // UUIDが生成され渡される
```

## ページネーション

DBの場合、`#[Pager]`アトリビュートでSELECTクエリーをページングすることができます。

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
}
```

`count()`で件数が取得でき、ページ番号で配列アクセスをするとページオブジェクトが取得できます。`Pages`はSQL遅延実行オブジェクトです。

```php
$pages = ($todoList)();
$cnt = count($pages);    // count()をした時にカウントSQLが生成されクエリーが行われます。
$page = $pages[2];       // 配列アクセスをした時にそのページのDBクエリーが行われます。

// $page->data           // sliced data
// $page->current;       // 現在のページ番号
// $page->total          // 総件数
// $page->hasNext        // 次ページの有無
// $page->hasPrevious    // 前ページの有無
// $page->maxPerPage;    // 1ページあたりの最大件数
// (string) $page        // ページャーHTML
```

## SqlQuery

`SqlQuery`はSQLファイルのIDを指定してSQLを実行します。実装クラスを用意して詳細な実装を行う時に使用します。

```php
class TodoItem implements TodoItemInterface
{
    public function __construct(
        private SqlQueryInterface $sqlQuery
    ) {}

    public function __invoke(string $id): array
    {
        return $this->sqlQuery->getRow('todo_item', ['id' => $id]);
    }
}
```

## get* メソッド

SELECT結果を取得するためには取得する結果に応じた`get*`を使います。

```php
$sqlQuery->getRow($queryId, $params);        // 結果が単数行
$sqlQuery->getRowList($queryId, $params);    // 結果が複数行
$statement = $sqlQuery->getStatement();       // PDO Statementを取得
$pages = $sqlQuery->getPages();              // ページャーを取得
```

Ray.MediaQueryは[Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule)を含んでいます。さらに低レイヤーの操作が必要な時はAura.Sqlの[Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder)やPDOを拡張した[Aura.Sql](https://github.com/auraphp/Aura.Sql)のExtended PDOをお使いください。[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule)も利用できます。

パラメーターインジェクションと同様、`DateTimeInterface`オブジェクトを渡すと日付フォーマットされた文字列に変換されます。

```php
$sqlQuery->exec('memo_add', [
    'memo' => 'run',
    'created_at' => new DateTime()
]);
```

他のオブジェクトが渡されると`toScalar()`または`__toString()`の値に変換されます。

### Ray.InputQueryとの連携

BEAR.ResourceでRay.InputQueryを利用している場合、InputクラスをMediaQueryのパラメーターとして直接渡すことができます。

```php
use Ray\InputQuery\Attribute\Input;

final class UserCreateInput
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age
    ) {}
}
```

```php
interface UserCreateInterface
{
    #[DbQuery('user_create')]
    public function add(UserCreateInput $input): void;
}
```

InputオブジェクトのプロパティがSQLパラメータに自動展開されます。

```sql
-- user_create.sql
INSERT INTO users (name, email, age) VALUES (:name, :email, :age);
```

この連携により、ResourceObjectからMediaQueryまで一貫して型安全なデータフローを実現できます。

## プロファイラー

メディアアクセスはロガーで記録されます。標準ではテストに使うメモリロガーがバインドされています。

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString(
        'query: todo_add({"id":"1","title":"run"})',
        (string) $this->log
    );
}
```

独自の[MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php)を実装して、各メディアクエリーのベンチマークを行ったり、インジェクトしたPSRロガーでログをすることもできます。

## アノテーション / アトリビュート

属性を表すのに[doctrineアノテーション](https://github.com/doctrine/annotations/)、[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)どちらも利用できます。次の2つは同じものです。

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```
