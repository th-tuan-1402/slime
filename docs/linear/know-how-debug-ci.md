---
title: "[Know-how] Debug & xử lý lỗi môi trường/CI (Docker, Composer, OpenAPI)"
linearDocumentId: "55895e5c-adbe-446a-a490-d5fca3bd303c"
linearUrl: "https://linear.app/bienhoa-hive/document/know-how-debug-and-xu-ly-loi-moi-truongci-docker-composer-openapi-8f2be26f09d6"
---

## Mục tiêu

Tài liệu này ghi lại **các sự cố đã gặp** trong quá trình làm backend (B1) và **cách debug/gỡ lỗi** + **chiến lược xử lý** để lần sau team xử lý nhanh, tránh lặp lại.

## TL;DR (những bài học quan trọng)

* Nếu QA/Dev không chạy được `php artisan` / `composer install` trong container: **kiểm tra mount** `./backend -> /var/www/html` trước, khả năng cao backend thiếu scaffold.
* CI fail ở `composer install` + `package:discover`: thường do `bootstrap/cache` không tồn tại hoặc không writable.
* CI fail `OpenAPI Validate`: thường do **thiếu package/provider của Scramble** hoặc **lint rule quá strict** so với spec hiện tại.
* PHPStan bị crash OOM: tăng `memory_limit` hoặc chạy với `--memory-limit`.

---

## Case 1: Docker mount khiến backend thiếu Laravel scaffold

### Triệu chứng

* Trong container `/var/www/html` chỉ có một phần: `app/`, `bootstrap/`, `config/`, `phpstan.neon`…
* Thiếu `composer.json`, `artisan`, `routes/`, `public/`, `tests/`, `database/` → không chạy được:
  * `composer install`
  * `php artisan test`
  * `phpstan`

### Cách debug (chiến lược)

* **B1**: Vào container và list root:
  * `docker compose exec -T app bash -lc 'ls -la /var/www/html | head -n 80'`
* **B2**: So sánh với host `backend/`:
  * `ls -la backend | head -n 80`
* **B3**: Xác nhận `docker-compose.yml` volume mount:
  * `./backend:/var/www/html`

### Fix

* Restore scaffold Laravel vào `backend/` (composer/artisan/routes/public/tests/database/…).

---

## Case 2: CI fail ở Composer post-autoload-dump (`package:discover`)

### Triệu chứng

* GitHub Actions log:
  * `The backend/bootstrap/cache directory must be present and writable.`

### Cách debug (chiến lược)

* **B1**: Mở log failed step:
  * `gh run view <run_id> --log-failed`
* **B2**: Nhìn đúng error message và vị trí (PackageManifest.php).

### Fix

* Tạo các thư mục cache/log cần thiết trước `package:discover` (hook `post-autoload-dump`).

---

## Case 3: CI fail OpenAPI Validate (Scramble)

### Triệu chứng

* `There are no commands defined in the "scramble" namespace.`

### Cách debug (chiến lược)

* **B1**: Chạy thử:
  * `php artisan list | grep -E '^  scramble'`
* **B2**: Nếu không có command → thiếu package hoặc provider không được load.
* **B3**: Với Laravel 11/12 nếu không có `config/app.php`, kiểm tra `bootstrap/app.php` đang dùng `->withProviders([...])`.

### Fix

* Thêm `dedoc/scramble` và register `\\Dedoc\\Scramble\\ScrambleServiceProvider::class` trong `bootstrap/app.php`.

---

## Case 4: CI fail OpenAPI lint (Redocly) vì rule quá strict

### Triệu chứng

* Redocly lỗi `security-defined` (bắt buộc có security root/operation)
* Warning khác: thiếu `info.license`, server là localhost, thiếu 4xx response

### Cách debug (chiến lược)

* Phân biệt rule nào là **error** vs **warning**.

### Fix

* Thêm `backend/redocly.yaml` để tắt các rule quá strict cho giai đoạn hiện tại.

---

## Case 5: PHPStan crash do thiếu memory

### Triệu chứng

* `PHPStan process crashed because it reached configured PHP memory limit: 128M`

### Cách debug (chiến lược)

* `php -i | grep -i memory_limit`

### Fix

* Tăng memory limit (vd `memory_limit=1G`) hoặc chạy phpstan với `--memory-limit`.

---

## Checklist khi gặp lỗi môi trường/CI

* **Docker**: container healthy, volume mount đúng, `/var/www/html` có `composer.json` + `artisan`
* **Composer/Artisan**: `bootstrap/cache` & `storage/*` tồn tại + writable
* **OpenAPI**: có `scramble:*` command; Redocly config phù hợp
* **PHPStan**: đủ memory

## Liên quan

* Blocker môi trường: **BIE-48**
* Issue bị block bởi môi trường: **BIE-40**, **BIE-16**

