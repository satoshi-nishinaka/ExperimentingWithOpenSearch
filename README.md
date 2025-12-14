# ExperimentingWithOpenSearch

## 概要
OpenSearch をローカルで立ち上げ、PHP クライアントから接続・インデックス操作を試すためのサンプルプロジェクトです。Docker Compose で OpenSearch と Dashboards を起動し、`php/Sample.php` で基本的な接続、インデックス作成、ドキュメント投入、検索を確認できます。

## 前提
- Docker / Docker Compose が利用可能であること
- PHP 8 系（Composer 利用）

## セットアップ
1. コンテナ起動（データは `./OpenSearch/data` にマウントされます）:
   ```sh
   docker compose up -d
   ```
2. PHP 依存インストール:
   ```sh
   cd php
   composer install
   ```

## 使い方
- サンプル実行（OpenSearch 起動済みであること）:
  ```sh
  cd php
  php Sample.php
  ```
- ログ確認:
  ```sh
  docker compose logs -f opensearch
  docker compose logs -f opensearch-dashboards
  ```
- Dashboards へアクセス: http://localhost:5601

## プロジェクト構成
- `compose.yml`: OpenSearch / Dashboards の定義。kuromoji プラグイン入りカスタムイメージを使用し、データをホスト `OpenSearch/data` に永続化。
- `OpenSearch/Dockerfile`: OpenSearch に kuromoji プラグインを追加。
- `php/`: PHP クライアントコード。`Sample.php` が実行例、`src/` が拡張用名前空間 `Satoshie\Php\`。
- `php/vendor/`: Composer により管理される依存ライブラリ。

## トラブルシュートのヒント
- インデックス作成が `FORBIDDEN/10/cluster create-index blocked` で失敗する場合、OpenSearch ノードのディスク空き不足（高水位到達）が原因です。`OpenSearch/data` の空き容量を確保したうえで再起動してください。
- `cluster.blocks.create_index` が `true` に残っている場合、空き容量を確保後に設定をクリアして再試行してください。

## ライセンス
このリポジトリは `LICENSE` の内容に従います。
