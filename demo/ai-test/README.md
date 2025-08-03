# BEAR.Resource AI Analysis Test

AIがBEAR.Resourceのセマンティックログを理解できるかテストする最小限のデモです。

## クイックスタート

```bash
php simple-bear-resource-demo.php
```

生成された `simple-semantic-log.json` を `ai-prompt.md` のプロンプトと共にAIに送信してください。

## ファイル構成

- `simple-bear-resource-demo.php` - ログ生成スクリプト（18行）
- `simple-semantic-log.json` - 生成されるセマンティックログ
- `ai-prompt.md` - AIテスト用プロンプトテンプレート
- `README.md` - このファイル

## テスト目的

AIの基本理解力をテスト：
- BEAR.Resourceのリソース指向アーキテクチャ
- HTTPセマンティクス
- セマンティックログ構造
- JSONスキーマ参照