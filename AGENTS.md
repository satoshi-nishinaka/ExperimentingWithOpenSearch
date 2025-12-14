# Repository Guidelines

## プロジェクト構成
- ルート: `compose.yml` が OpenSearch + Dashboards を起動、`OpenSearch/Dockerfile` が kuromoji プラグイン追加済みのカスタムイメージを生成。
- PHP クライアント: `php/` 配下。`Sample.php` は動作例、`src/` が `Satoshie\Php\` 名前空間の拡張先、`vendor/` は Composer 管理。
- 追加実装は `php/src/` に機能単位で配置し、1 ファイル 1 クラスを基本とする。

## ビルド・開発コマンド
- OpenSearch 起動/停止: リポジトリ直下で `docker compose up -d` / `docker compose down`。
- ログ確認: `docker compose logs -f opensearch` / `... opensearch-dashboards`。
- 依存インストール: `cd php && composer install`。
- サンプル実行（ローカル OpenSearch 前提）: `cd php && php Sample.php`。

## コーディングスタイルと命名
- PSR-12 準拠: 4 スペースインデント、`declare(strict_types=1);`、メソッド・変数は camelCase、クラスは StudlyCaps。ディレクトリ構造と名前空間を一致させる。
- 設定値は YAML/ENV に分離し、資格情報のハードコードは禁止。
- 関数は小さく目的明確にし、意図が分かりにくい箇所のみ短いコメントを付す。

## テスト指針
- 現状自動テストは未整備。新機能追加時は `php/tests/` にテストを配置する方針。
- PHPUnit を導入する場合の想定コマンド: `cd php && ./vendor/bin/phpunit`。
- OpenSearch 連携テストでは使用インデックス名やクリーンアップ手順をテスト内コメントに明記する。

## コミットと PR
- コミットメッセージは短い命令形（例: `Add kuromoji plugin`, `Document sample run`）。関連 Issue があれば `#番号` を添える。
- PR には目的、再現手順（`docker compose ...`, `composer install` など）、検証コマンド結果、UI 変更時はスクリーンショットを含める。

## セキュリティと構成
- `compose.yml` の初期パスワードはローカル用。共有・公開前に環境変数や `.env` で上書きし、必要なら削除・ローテーションする。
- 秘密情報のコミット禁止。リモート公開時はセキュリティプラグインと TLS を有効化する。

## エージェント対応ルール
- すべて日本語で応答し、冒頭で「AGENTS.md のルールに沿って対応しています」と宣言する。
- 応答前に読んだルール数を「X のルールに沿って返信します」と明示する。
- 結論先出しで記述し、承認が必要な作業は提案→承認→実施の順を厳守する。
- ユーザー指示が不明確な場合は曖昧な変更を避け、必要な承認内容を具体的に確認する。
- 不要な変更や指示逸脱を行わず、改善点は必ず提案して同意を得てから実行する。
