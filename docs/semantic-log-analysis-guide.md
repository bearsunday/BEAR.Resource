# セマンティックログ分析ガイド

## 概要

このドキュメントは、BEAR.Resourceのセマンティックログを使用してシステムパフォーマンス分析を行う際の包括的なガイドです。LLM（AI）による分析を想定して設計されており、効率的で正確な分析手法を提供します。

## セマンティックログの基本構造

### 最適設計
```json
{
  "schemaUrl": "/path/to/semantic-log.json",
  "describedBy": "/docs/semantic-log-analysis-guide.md",
  "links": [
    {
      "rel": "https://bearsunday.github.io/BEAR.Resource/docs/link-relations#ai-docs",
      "href": "/docs/llms.txt",
      "title": "BEAR.Resource Framework Overview"
    }
  ],
  "open": {
    "id": "req_1",
    "method": "GET", 
    "uri": "app://self/user-list-with-problems?page=1"
  },
  "events": [
    {
      "open": { 
        "id": "req_2", 
        "uri": "app://self/user/profile?id=1", 
        "parentId": "req_1" 
      }
    },
    {
      "close": { "id": "req_2" }
    }
  ],
  "close": {
    "id": "req_1",
    "code": 200,
    "body": { /* 実行結果 */ },
    "xhprofFile": "/path/to/profile.xhprof",
    "xdebugTraceFile": "/path/to/trace.xt.gz"
  }
}
```

## 分析の核心原則

### 1. ゼロ冗長性原則

以下の情報は**既存ツールで取得可能なため不要**：

- ❌ `resourceClass` → XdebugトレースとXHProfに含有
- ❌ `caller情報` → Xdebugトレースに含有
- ❌ `trace配列` → Xdebugトレースファイルと重複
- ❌ `メモリ情報` → XHProfに詳細データ存在
- ❌ `embed/link明示` → bodyの構造で表現済み
- ❌ `プロジェクト構造` → Claude Code環境で取得可能

### 2. 役割分担の明確化

| コンポーネント | 責務 |
|---|---|
| セマンティックログ | 実行結果記録、プロファイルへのポインター、実行グラフ追跡 |
| XHProf | 詳細パフォーマンスメトリクス（時間、メモリ、呼び出し回数） |
| Xdebugトレース | 完全な実行フロー、呼び出し元、クラス/メソッド情報 |

## 分析手順

### Step 1: スキーマとフレームワーク理解
```
1. schemaUrlから構造定義を確認
2. linksからフレームワーク知識（llms.txt）を取得
3. URIマッピング規則を理解
```

### Step 2: 実行結果の構造分析
```json
"body": {
  "users": [...],           // メインデータ
  "external_data": {...},   // 外部API統合
  "page": 1,               // ページネーション
  "total": 2               // 関連データ
}
```

**分析ポイント**：
- ネストした構造からembed関係を推論
- 配列の要素数からN+1問題の可能性を検討
- データ構造の複雑さからパフォーマンス影響を予測

### Step 3: プロファイルデータ分析

#### XHProfデータの読み方
```php
s:114:"BEAR\\Resource\\PhpClassInvoker::invoke==>...::onGet";
a:5:{
  s:2:"ct";i:1;        // call count（呼び出し回数）
  s:2:"wt";i:276611;   // wall time（実行時間μs）
  s:3:"cpu";i:7662;    // CPU time（CPU時間μs）
  s:2:"mu";i:52968;    // memory usage（メモリ使用量bytes）
  s:3:"pmu";i:19088;   // peak memory（ピークメモリbytes）
}
```

#### 重要メトリクス
- **実行時間分析**: `wt`値の大きい関数がボトルネック
- **呼び出し回数**: `ct`値でN+1問題を定量的に検証
- **メモリ効率**: `mu`/`pmu`でメモリリークやスパイクを検出

### Step 4: Xdebugトレース分析
```
TRACE START [timestamp]
    time -> Class::method(...) /path/to/file.php:line
```

**活用方法**：
- 呼び出し元の特定（テスト vs 本番 vs CLI）
- 実行フローの詳細把握
- クラス/メソッド名の確認

### Step 5: 実行グラフ分析（events）

#### N+1問題の検出
```javascript
// プロファイル取得の回数をカウント
events.filter(e => 
  e.open?.uri.includes('user/profile') && 
  e.open?.parentId === 'main_request_id'
).length
```

#### 並列化可能性の評価
```javascript
// 同レベルのリクエストは並列実行可能
events.filter(e => e.open?.parentId === 'main_request_id')
```

## 一般的な問題パターンと検出方法

### 1. N+1問題

**検出方法**：
```
- レスポンス配列の要素数 × 関連リソース呼び出し回数
- XHProf: ct値が配列要素数と一致
- events: 同種URIの複数open/close
```

**改善提案**：
```php
// Before: N+1
foreach ($users as $user) {
    $user['profile'] = $this->getUserProfile($user['id']);
}

// After: 一括取得
$profiles = $this->getBulkUserProfiles(array_column($users, 'id'));
```

### 2. 外部API遅延

**検出方法**：
```
- XHProf: 特定関数の異常に高いwt値
- 外部ドメインへのHTTP呼び出しパターン
```

**改善提案**：
```php
// 非同期化、キャッシュ、タイムアウト設定
$promise = $this->httpClient->getAsync($url);
```

### 3. CPU集約処理

**検出方法**：
```
- XHProf: cpu値がwt値に近い関数
- ループ内での重い計算処理
```

**改善提案**：
```php
// キャッシュ、最適化、処理分散
$cached = $this->cache->remember($key, fn() => $this->heavyCalculation());
```

## パフォーマンス分析の定量化

### 実行時間の内訳計算
```
総実行時間: XHProf main関数のwt値
ボトルネック特定: wt値の大きい順にソート
改善効果予測: ボトルネック削減時間の計算
```

### スケーラビリティ予測
```
現在のデータ量での実行時間を基準に：
- O(1): 定数時間（外部API呼び出し）
- O(n): 線形時間（N+1問題）
- O(n²): 二次時間（ネストループ）
```

## 改善提案の優先順位付け

### 1. 影響度評価
```
高影響: 総実行時間の30%以上を占める処理
中影響: 10-30%を占める処理  
低影響: 10%未満の処理
```

### 2. 実装コスト評価
```
低コスト: 設定変更、クエリ最適化
中コスト: ライブラリ追加、アーキテクチャ変更
高コスト: インフラ変更、大規模リファクタリング
```

### 3. ROI計算
```
ROI = 性能改善効果 / 実装コスト
```

## LLM分析における認知最適化

### 段階的分析アプローチ
1. **構造理解**: スキーマとフレームワーク知識の取得
2. **証拠収集**: プロファイルデータの数値確認
3. **因果関係**: 実装コードとの対応確認
4. **システム理解**: 実行グラフと依存関係の把握
5. **最適化設計**: 具体的改善案の策定

### 認知負荷軽減のポイント
- **構造化情報**: スキーマによる事前理解
- **直接リンク**: プロファイルファイルへの即座アクセス
- **関連知識**: フレームワーク仕様への自動参照
- **証拠統合**: 複数ツールからの一致した証拠

## ベストプラクティス

### 1. 分析の完全性確保
- セマンティックログ、XHProf、Xdebugトレースの三点検証
- 仮説と証拠の一致確認
- 改善提案の実現可能性評価

### 2. 効率的な分析フロー
- TodoListによる段階的タスク管理
- 並行ツール呼び出しによる情報収集
- 冗長性チェックによる重複排除

### 3. 実用的な改善提案
- 定量的効果の算出
- 実装コストの見積もり
- 監視すべきメトリクスの提示

## 実例に基づく分析結果

### 検出された問題
1. **N+1問題**: 20ユーザー × 2クエリ = 40回DB呼び出し
2. **外部API遅延**: 103ms同期呼び出し（総時間の37.5%）
3. **CPU集約処理**: ループ内重処理による26ms消費

### 改善効果予測
- N+1解決: 31ms削減（11.3%改善）
- 外部API非同期化: 103ms削減（37.5%改善）
- 処理統合: 20ms削減（7.2%改善）
- **合計期待効果**: 154ms削減（55.8%改善）

## 設計哲学

セマンティックログは「**認知増幅システム**」として機能します：

1. **構造化による理解の高速化**
2. **証拠統合による確信度向上**
3. **関係性発見の自動化**
4. **システム思考の促進**

この設計により、複雑なシステムの深層構造を短時間で把握し、具体的な最適化戦略を策定できます。

---

*このガイドは、実際のセマンティックログ分析セッションから得られた知見に基づいて作成されています。*