# 【修正版】プロファイリング処理のDI化実装プロンプト

## 重要な前提確認
CompleteContextとErrorContextは**BEAR.Resource側**のクラスであり、**Koriym.SemanticLogger側**には存在しません。

ロガー側に存在するのは：
- `AbstractContext` - 基底クラス
- `FakeContext` - テスト用クラス

## 実装方針の修正

### Option 1: ロガー側でインターフェースのみ定義
BEAR.Resource側で使用するProfilerInterfaceとProfileResultをロガー側で定義し、BEAR.Resource側で実装・使用する

### Option 2: BEAR.Resource側で完結
プロファイリング処理は完全にBEAR.Resource側で実装し、ロガー側は関与しない

## 推奨案: **Option 1 - インターフェース定義**

### ロガー側での作業

#### 1. インターフェース定義
```php
// src/ProfilerInterface.php
<?php

declare(strict_types=1);

namespace Koriym\SemanticLogger;

interface ProfilerInterface
{
    public function stop(string $uri): ProfileResult;
}
```

#### 2. 結果クラス定義
```php
// src/ProfileResult.php
<?php

declare(strict_types=1);

namespace Koriym\SemanticLogger;

final class ProfileResult
{
    public function __construct(
        public readonly ?string $xhprofFile,
        public readonly ?string $xdebugTraceFile,
    ) {}
}
```

### BEAR.Resource側での作業

#### 1. 実装クラス作成
```php
// src/SemanticLog/VerboseProfiler.php
<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\ProfilerInterface;
use Koriym\SemanticLogger\ProfileResult;

final class VerboseProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        $xhprofFile = null;
        $xdebugTraceFile = null;
        
        // XHProf停止処理
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sprintf(
                '%s/xhprof_%s_%s.xhprof',
                sys_get_temp_dir(),
                str_replace(['/', ':', '?'], '_', $uri),
                uniqid('', true),
            );
            
            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $xhprofFile = $filename;
            }
        }
        
        // Xdebugトレース停止処理
        $traceFile = @xdebug_stop_trace();
        if (is_string($traceFile) && file_exists($traceFile)) {
            $xdebugTraceFile = $traceFile;
        }
        
        return new ProfileResult($xhprofFile, $xdebugTraceFile);
    }
}
```

#### 2. CompleteContext更新（BEAR.Resource側）
```php
// 既存のプロファイリング処理を削除してDI注入に変更
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext,
        ProfilerInterface $profiler,  // ← DI追加
    ) {
        // 既存のドメインロジック
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view ?? '';
        
        // プロファイリング処理（重複削除）
        $profileResult = $profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

#### 3. ErrorContext更新（BEAR.Resource側）
```php
final class ErrorContext extends AbstractContext
{
    public function __construct(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
        ?ProfilerInterface $profiler = null,  // ← DI追加
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();
        
        $this->xhprofFile = null;
        $this->xdebugTraceFile = null;
        
        if ($openContext === null || $profiler === null) {
            return;
        }
        
        // プロファイリング処理（重複削除）
        $profileResult = $profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

#### 4. DIコンテナ設定（BEAR.Resource側）
```php
$this->bind(ProfilerInterface::class)->to(VerboseProfiler::class)->in(Scope::SINGLETON);
```

## 作業分担

### ロガー側（Koriym.SemanticLogger）で実施
- [ ] ProfilerInterface の定義
- [ ] ProfileResult クラスの定義

### BEAR.Resource側で実施  
- [ ] VerboseProfiler の実装
- [ ] CompleteContext からの重複プロファイリング処理削除・DI注入
- [ ] ErrorContext からの重複プロファイリング処理削除・DI注入
- [ ] DIコンテナ設定

## 期待される効果

1. **重複削除**: CompleteContextとErrorContextの同一処理を一元化
2. **責務分離**: プロファイリングロジックを独立したサービスに分離
3. **テスタビリティ**: ProfilerInterfaceをモックしてテスト可能
4. **拡張性**: 将来的な新しいプロファイラー追加が容易

この修正版プロンプトにより、実際のコードベース構造に合わせた適切な実装が可能になります。