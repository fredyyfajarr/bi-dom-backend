# BI DOM Backend

Backend REST API for DOM Social Hub Business Intelligence. The API powers dashboard analytics, transaction imports, invoice views, master products, recipe-based inventory usage, and SMA inventory forecasting.

## Core Features

- **Authentication:** Sanctum token authentication for manager/kasir access.
- **Dashboard analytics:** revenue, COGS, net profit, top products, category mix, peak hours, market basket, and KPI summaries.
- **Invoice API:** paginated invoices with receipt number, transaction date, total amount, payment method, and detail endpoint.
- **CSV transaction import:** supports simple transaction CSV and itemized receipt CSV.
- **Partial itemized import:** valid receipts are imported even when other receipts in the same CSV contain unknown products.
- **Payment method support:** imported and stored transaction payment methods such as `CASH`, `QRIS`, and `DEBIT`; values are normalized to uppercase.
- **Master product management:** products have category, selling price, COGS, and required recipe materials.
- **Recipe-based stock deduction:** itemized imports reduce inventory stock from `product_inventory.usage_qty * sold quantity`.
- **Inventory alert forecasting:** predicts next-week ingredient usage using a 30-day SMA of actual recipe usage from transaction details, with explicit usage basis metadata.
- **FrankenPHP / Laravel Octane runtime:** intended to run as the backend service on port `8000`.

## Inventory Forecasting Flow

The forecasting feature is not a standalone module. It connects transaction import, master product, recipe materials, and inventory alert.

1. Master Product stores menu items and their ingredient recipes in `product_inventory`.
2. CSV import creates `transactions` and `transaction_details`.
3. For each imported product row, backend calculates ingredient usage and deducts `inventories.current_stock`.
4. Inventory Alert reads the last 30 days of transaction details and recipe usage, anchored to the latest imported transaction date.
5. Predicted usage is calculated as:

```text
next_week_usage = (total_ingredient_usage_last_30_days_from_latest_csv_date / 30) * 7
```

6. Alert status is calculated as:

```text
Kritis if current_stock - predicted_usage <= min_stock
```

If the database only has simple transactions without product details, the alert falls back to `usage_per_trx`.

Every Inventory Alert row also returns `usage_basis`:

- `RECIPE_SMA_30D`: ingredient has recipe-based usage history in the last 30 days.
- `NO_RECENT_USAGE`: recipe-based history exists in the system, but this ingredient was not used in the last 30 days.
- `TRX_AVG_FALLBACK`: no recipe-based usage history is available yet, so the system falls back to `usage_per_trx`.

Products are required to have at least one recipe material. This prevents menu items from bypassing stock deduction and forecasting.

Because the project currently uses manual CSV imports as the transaction source, the forecast window follows the latest transaction date in the imported data. This keeps demo or historical CSV files meaningful even when their transaction dates do not match the server's current date.

## Menu And Seed Data

The seeders include DOM Social Hub menu data and estimated recipe materials:

- `database/seeders/ProductSeeder.php`
- `database/seeders/InventorySeeder.php`

Current seeded demo catalog:

- 45 active menu products
- 42 inventory materials
- Categories: Coffee, Non-Coffee, Tea, Mocktail, Frappe, Main Course, Finger Foods

## CSV Samples

Sample import files are stored in `database/samples`.

- `import-transactions-dom-menu-inventory-alert-test.csv`
  - Internal validation file.
  - Uses receipt prefix `DOM-ALERT-*`.
  - Designed to trigger inventory alerts.
- `import-transactions-dom-menu-inventory-alert-demo-fresh.csv`
  - Demo file for manual presentation testing.
  - Uses fresh receipt prefix `DOM-DEMO-PRESENT-*`.
  - Do not pre-import this file before a live demo if you want all rows to be accepted.

Itemized CSV format:

```csv
receipt_no,trx_date,product_name,qty,subtotal,payment_method
DOM-DEMO-PRESENT-001,2026-06-12 09:05:00,DOM's Original,8,200000,QRIS
DOM-DEMO-PRESENT-001,2026-06-12 09:05:00,Kopi Latte,6,150000,QRIS
```

`payment_method` is optional. If omitted, the backend defaults to `CASH`; if provided, it is normalized to uppercase.

For itemized CSV, validation runs per receipt. If a receipt contains a `product_name` that is not available in Master Product, that whole receipt is rejected, but other valid receipts in the same file are still imported. Rejected receipts are returned in `rejected_receipts` with the missing product names.

## Main API Endpoints

Base URL:

```text
http://127.0.0.1:8000/api/v1
```

Common endpoints:

- `POST /login`
- `POST /logout`
- `GET /dashboard`
- `GET /dashboard/categories-list`
- `GET /invoices`
- `GET /invoices/{id}`
- `POST /import`
- `GET /products`
- `POST /products`
- `PUT /products/{id}`
- `DELETE /products/{id}`
- `GET /inventory/alerts`
- `POST /inventory/update-stock`
- `POST /inventory/items`

Opening `/api/v1` directly in a browser returns `404` because it is only an API prefix.

## Local Development

Install dependencies:

```bash
composer install
```

Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=InventorySeeder
php artisan db:seed --class=ProductSeeder
```

Run standard Laravel server:

```bash
php artisan serve
```

Run with Octane / FrankenPHP:

```bash
php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000
```

In the project workspace, the VS Code build task is intended to run the backend through the Docker/Octane setup so the API listens on port `8000`.

## Validation

Run unit tests:

```bash
php artisan test --testsuite=Unit
```

Run all tests:

```bash
php artisan test
```

## Copyright

Copyright (c) 2026 Fredy Fajar Adi Putra. All Rights Reserved.
