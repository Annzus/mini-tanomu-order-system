# Mini B2B Order Management System

[English](README.md) | [日本語](README.ja.md)

Laravel 11 と Vue 3 で構築した、一般的な B2B 卸売受注ワークフローを題材にした demo です。
TypeScript、Vite、MySQL、Laravel Sanctum を使用しています。

このプロジェクトは、ロールベースの受注ワークフローを示す full-stack demo です。得意先ユーザーは商品閲覧、注文作成、注文履歴確認を行い、管理者ユーザーは全注文の確認、ステータス更新、CSV 出力を行います。認証、得意先別価格、トランザクションによる注文作成、注文明細の価格スナップショットはバックエンド側で処理します。

## 機能

得意先ユーザー:

- token 認証でログインできます。
- 有効な商品を閲覧できます。
- バックエンドで解決された得意先別価格を確認できます。
- 商品一覧から注文を作成できます。
- 自分の注文履歴と注文詳細を確認できます。

管理者ユーザー:

- token 認証でログインできます。
- 全得意先の注文を確認できます。
- ステータスで注文を絞り込めます。
- 注文詳細と注文明細の価格スナップショットを確認できます。
- 許可されたステータス遷移に沿って注文ステータスを更新できます。
- 注文データを CSV 出力できます。CSV は注文明細 1 行につき 1 行です。

重要な業務ロジックはバックエンドが担当します。

- フロントエンドから送信された価格は信用しません。
- 注文作成はデータベーストランザクション内で実行します。
- 注文明細には商品コード、商品名、単位、単価、数量、小計のスナップショットを保存します。

## プロジェクト構成

```text
mini-tanomu-order-system/
  backend/   Laravel 11 API application
  frontend/  Vue 3 + TypeScript + Vite application
  docs/      Development steps and project notes
```

主なバックエンドディレクトリ:

```text
backend/routes/api.php
backend/app/Http/Controllers/Api
backend/app/Http/Requests
backend/app/Http/Resources
backend/app/Models
backend/app/Services
backend/database/migrations
backend/database/seeders/DatabaseSeeder.php
backend/tests/Feature
```

主なフロントエンドディレクトリ:

```text
frontend/src/api
frontend/src/components
frontend/src/pages
frontend/src/router
frontend/src/stores
frontend/src/types
frontend/src/style.css
```

## 必要環境

- PHP 8.3
- Composer
- MySQL 8.x
- Node.js and npm

ローカル開発用データベース:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_tanomu_order_system
DB_USERNAME=root
DB_PASSWORD=
```

## バックエンド起動

リポジトリルートから:

```powershell
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

この Windows 環境で `php` が PATH に入っていない場合:

```powershell
& "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

ヘルスチェック:

```text
http://127.0.0.1:8000/api/health
```

## フロントエンド起動

別ターミナルで:

```powershell
cd frontend
npm install
npm run dev -- --host 127.0.0.1 --port 5173
```

フロントエンド URL:

```text
http://127.0.0.1:5173/login
```

API ベース URL:

```text
frontend/.env.example
VITE_API_BASE_URL=http://localhost:8000/api
```

## Demo アカウント

```text
Admin
Email: admin@example.com
Password: password

Customer A
Email: customer-a@example.com
Password: password

Customer B
Email: customer-b@example.com
Password: password
```

## データベース設計

主なテーブル:

- `users`: `admin` / `customer` ロールを持つログインユーザー。
- `customers`: 得意先マスタ。
- `products`: 商品マスタ、標準価格、在庫数量、有効フラグ。
- `customer_product_prices`: 得意先別の商品価格。
- `orders`: 注文ヘッダー。得意先、ステータス、希望納品日、備考、合計金額、注文日時を保持します。
- `order_items`: 注文明細スナップショット。商品コード、商品名、単位、単価、数量、小計を保持します。
- `personal_access_tokens`: Laravel Sanctum API token。

Demo データ:

```text
backend/database/seeders/DatabaseSeeder.php
```

## API 概要

認証:

```text
POST /api/login
POST /api/logout
GET  /api/me
```

得意先:

```text
GET  /api/products
GET  /api/products/{product}
POST /api/orders
GET  /api/orders
GET  /api/orders/{order}
```

管理者:

```text
GET   /api/admin/orders
GET   /api/admin/orders/{order}
PATCH /api/admin/orders/{order}/status
GET   /api/admin/orders/export
```

## 注文ステータス遷移

許可される管理者ステータス更新:

```text
pending   -> confirmed | cancelled
confirmed -> preparing | cancelled
preparing -> delivered
delivered -> final
cancelled -> final
```

不正なステータス遷移はバリデーションエラーになります。

## テスト

バックエンド:

```powershell
cd backend
php artisan test
```

フロントエンド:

```powershell
cd frontend
npm run build
```

現在のバックエンドテスト対象:

- 認証 API
- 商品 API と得意先別価格
- 得意先の注文作成、注文履歴、注文詳細、得意先分離
- バックエンド価格計算と注文明細スナップショット
- 管理者の注文一覧、詳細、ステータス更新、CSV 出力

## 現在のスコープ

実装済み:

- 得意先注文フロー
- 管理者受注管理フロー
- CSV 出力
- Demo アプリの日本語 UI
- MySQL demo データ

v1 では未実装:

- 商品管理 UI
- 得意先管理 UI
- 注文ステータス履歴
- 商品検索とカテゴリ
- 管理者ダッシュボード
- Docker Compose
- Redis または Valkey
- デプロイ
- メール、LINE、FAX-OCR、決済、実在庫引当
