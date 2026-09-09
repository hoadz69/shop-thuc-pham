# Thực phẩm Thủy Trang Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hoàn thiện website bán thực phẩm WordPress/WooCommerce theo brief, có backup ngoài VPS, child theme, catalog mẫu, checkout COD, QR, PWA và tài liệu vận hành/khôi phục.

**Architecture:** Repository local giữ scripts, child theme, plugin tự phát triển, dữ liệu mẫu và tài liệu; VPS chỉ là runtime WordPress cùng database/uploads. Mọi thay đổi server đi qua script có preflight, backup, checksum, deploy có phạm vi và smoke test; database/uploads không được đưa vào Git.

**Tech Stack:** PowerShell 7, OpenSSH/PuTTY, Bash, WP-CLI, WordPress, WooCommerce, Blocksy child theme, PHP 8.3, CSS/JavaScript thuần, Pester, Git.

---

## Phạm vi file

- `scripts/lib/Project.Common.ps1`: nạp cấu hình, kiểm tra fingerprint/đường dẫn và chạy SSH/SCP mà không log secret.
- `scripts/Test-Connection.ps1`: preflight chỉ đọc cho VPS.
- `scripts/Backup-Baseline.ps1`: tạo dump/database, archive uploads/source, manifest và checksum trên VPS rồi tải về local.
- `scripts/Verify-Backup.ps1`: so checksum, kiểm tra gzip/tar và xác minh dump có cấu trúc SQL hợp lệ.
- `scripts/Install-WpCli.ps1`: cài WP-CLI từ nguồn chính thức sau backup.
- `scripts/Configure-WordPress.ps1`: cấu hình locale/timezone/permalink/VNĐ/COD và tạo trang bằng thao tác idempotent.
- `scripts/Seed-Catalog.ps1`: tạo bảy danh mục và 10–15 sản phẩm mẫu từ dữ liệu version-control.
- `scripts/Deploy.ps1`: deploy đúng child theme/plugin dự án, giữ owner `www:www` và rollback khi smoke test lỗi.
- `scripts/Smoke-Test.ps1`: kiểm tra HTTP, WordPress, catalog, giỏ hàng, checkout COD, QR và PWA theo phạm vi có thể tự động hóa.
- `tests/powershell/*.Tests.ps1`: kiểm thử offline cho script; SSH được mock, không chạm VPS.
- `theme/blocksy-child/*`: toàn bộ giao diện và WooCommerce hooks của dự án.
- `plugin/tt-product-qr/*`: QR sản phẩm và template in tem, không sửa WooCommerce/core.
- `data/categories.csv`, `data/products.csv`: dữ liệu mẫu có khóa ổn định để nhập lặp lại.
- `docs/operations/*.md`: vận hành sản phẩm/đơn hàng, deploy, backup/restore và bàn giao.

## Giai đoạn A — Bảo vệ hiện trạng

### Task 1: Chuẩn hóa thư viện kết nối và kiểm thử offline

**Files:**
- Create: `scripts/lib/Project.Common.ps1`
- Create: `tests/powershell/Project.Common.Tests.ps1`
- Modify: `config/local.example.ps1`

- [x] **Step 1: Viết test thất bại cho cấu hình bắt buộc**

Test phải dot-source thư viện, tạo một file cấu hình tạm thiếu `ProjectHostKey`, gọi `Import-ProjectConfig`, và xác nhận lỗi chứa `ProjectHostKey`. Test thứ hai truyền `ProjectWebRoot=/` và xác nhận bị từ chối.

```powershell
Describe 'Import-ProjectConfig' {
    It 'rejects a config without a pinned host key' {
        $path = Join-Path $TestDrive 'local.ps1'
        Set-Content $path '$ProjectServerHost="127.0.0.1";$ProjectSshPort=2222;$ProjectSshUser="root";$ProjectSshKeyPath="C:\key.ppk";$ProjectWebRoot="/www/wwwroot/site"'
        { Import-ProjectConfig -Path $path } | Should -Throw '*ProjectHostKey*'
    }
    It 'rejects a broad web root' {
        $path = Join-Path $TestDrive 'local.ps1'
        Set-Content $path '$ProjectServerHost="127.0.0.1";$ProjectSshPort=2222;$ProjectSshUser="root";$ProjectSshKeyPath="C:\key.ppk";$ProjectHostKey="SHA256:test";$ProjectWebRoot="/"'
        { Import-ProjectConfig -Path $path } | Should -Throw '*ProjectWebRoot*'
    }
}
```

- [x] **Step 2: Chạy test và xác nhận đỏ**

Run: `Invoke-Pester tests/powershell/Project.Common.Tests.ps1 -Output Detailed`

Expected: FAIL vì `Import-ProjectConfig` chưa tồn tại.

- [x] **Step 3: Cài đặt tối thiểu thư viện**

`Import-ProjectConfig` phải nạp file trong scope riêng, trả về object chỉ chứa sáu trường public, kiểm tra key tồn tại, port bằng `2222`, key file có thật, fingerprint bắt đầu bằng `SHA256:` và web root khớp `^/www/wwwroot/[^/]+$`. `Invoke-ProjectSsh` và `Copy-ProjectScp` phải dùng argument array, ghim fingerprint và không nối secret vào output.

- [x] **Step 4: Chạy test và kiểm tra secret hygiene**

Run: `Invoke-Pester tests/powershell/Project.Common.Tests.ps1 -Output Detailed`

Expected: PASS.

Run: `git grep -nEi '(password|private.?key|token)\s*[=:]\s*[^<$]' -- ':!docs/project-brief.md' ':!AGENTS.md'`

Expected: không có credential thật; comment và tên biến cấu hình mẫu được phép sau khi xem thủ công.

- [x] **Step 5: Commit**

```powershell
git add config/local.example.ps1 scripts/lib/Project.Common.ps1 tests/powershell/Project.Common.Tests.ps1
git commit -m "build: add safe server connection helpers"
```

### Task 2: Preflight chỉ đọc và inventory có thể lặp lại

**Files:**
- Create: `scripts/Test-Connection.ps1`
- Create: `scripts/Get-ServerInventory.ps1`
- Create: `tests/powershell/ServerInventory.Tests.ps1`
- Modify: `docs/server-inventory.md`

- [x] **Step 1: Viết test thất bại cho các lệnh chỉ đọc**

Mock `Invoke-ProjectSsh`; xác nhận script gọi `df -P`, `stat`, đọc phiên bản WordPress, liệt kê theme/plugin, đếm product/order/page/upload và không chứa `rm`, `mv`, `sed -i`, `wp option update`, `wp plugin install`.

- [x] **Step 2: Chạy test và xác nhận đỏ**

Run: `Invoke-Pester tests/powershell/ServerInventory.Tests.ps1 -Output Detailed`

Expected: FAIL vì hai script chưa tồn tại.

- [x] **Step 3: Cài preflight và inventory**

`Test-Connection.ps1` phải xác minh hostname, UID, disk free, web root, owner/group và các binary hiện có. `Get-ServerInventory.ps1` phải trả JSON về stdout; chỉ tham số `-UpdateDoc` mới ghi phần “Khảo sát tự động gần nhất” vào inventory local.

- [x] **Step 4: Chạy offline tests rồi khảo sát server**

Run: `Invoke-Pester tests/powershell/ServerInventory.Tests.ps1 -Output Detailed`

Expected: PASS.

Run: `pwsh -File scripts/Test-Connection.ps1`

Expected: kết nối đúng fingerprint, port `2222`, web root đúng và không thay đổi server.

Run: `pwsh -File scripts/Get-ServerInventory.ps1 -UpdateDoc`

Expected: số liệu khớp hoặc mọi drift được ghi rõ, không có secret.

- [x] **Step 5: Commit**

```powershell
git add scripts/Test-Connection.ps1 scripts/Get-ServerInventory.ps1 tests/powershell/ServerInventory.Tests.ps1 docs/server-inventory.md
git commit -m "ops: automate read-only server inventory"
```

### Task 3: Tạo baseline backup và tải ra ngoài VPS

**Files:**
- Create: `scripts/Backup-Baseline.ps1`
- Create: `scripts/remote/backup-baseline.sh`
- Create: `scripts/Verify-Backup.ps1`
- Create: `tests/powershell/BackupBaseline.Tests.ps1`
- Create: `tests/powershell/VerifyBackup.Tests.ps1`
- Modify: `.gitignore`
- Modify: `docs/sessions/CURRENT.md`

- [x] **Step 1: Viết test thất bại cho fail-closed backup**

Mock SSH/SCP và xác nhận: disk check chạy trước dump; remote artifact nằm dưới `/www/backup/site/thuc-pham-thuy-trang/<UTC timestamp>`; local artifact nằm dưới thư mục ignored `backups/`; khi checksum lệch thì script dừng trước mọi thay đổi khác.

- [x] **Step 2: Chạy test và xác nhận đỏ**

Run: `Invoke-Pester tests/powershell/BackupBaseline.Tests.ps1 -Output Detailed`

Expected: FAIL vì script backup chưa tồn tại.

- [x] **Step 3: Cài remote backup script**

Script Bash phải dùng `set -Eeuo pipefail`, kiểm tra web root tuyệt đối, đọc DB credentials bên trong VPS qua WordPress bootstrap mà không in chúng, chạy `mysqldump --single-transaction --quick --skip-lock-tables`, tạo `uploads.tar.gz` và `site-source.tar.gz` loại trừ cache/backup, sinh `environment.txt` cùng `SHA256SUMS`, rồi đặt quyền `600` cho dump/manifest và `700` cho thư mục backup.

- [x] **Step 4: Viết test thất bại và cài verifier local**

Tạo fixtures checksum đúng/sai trong `$TestDrive`; mock các chương trình archive; xác nhận checksum sai, archive hỏng hoặc dump không có cả marker `CREATE TABLE` và `INSERT INTO` đều làm verifier thoát khác `0`.

Run: `Invoke-Pester tests/powershell/VerifyBackup.Tests.ps1 -Output Detailed`

Expected trước cài đặt: FAIL vì `Verify-Backup.ps1` chưa tồn tại.

`Verify-Backup.ps1` phải nhận tham số bắt buộc `-BackupPath`, resolve thành đường dẫn tuyệt đối bên dưới thư mục `backups`, đọc từng dòng `<sha256><hai dấu cách><filename>` trong `SHA256SUMS`, chặn path traversal, so bằng `Get-FileHash -Algorithm SHA256`, rồi gọi `gzip -t` và `tar -tzf`. SQL được đọc theo stream và phải chứa cả `CREATE TABLE` lẫn `INSERT INTO`; script trả exit code khác `0` ngay tại lỗi đầu tiên.

Run: `Invoke-Pester tests/powershell/VerifyBackup.Tests.ps1 -Output Detailed`

Expected sau cài đặt: PASS.

- [x] **Step 5: Cài orchestration local và ignore artifact**

`Backup-Baseline.ps1` phải upload script vào một file tạm cụ thể dưới `/tmp`, chạy nó, luôn xóa đúng file tạm bằng trap, tải toàn bộ artifact về thư mục `backups` có tên timestamp do script trả về, gọi `Verify-Backup.ps1`, và chỉ in đường dẫn/timestamp/kích thước/checksum. Thêm `/backups/` vào `.gitignore`.

- [x] **Step 6: Chạy offline tests và tạo backup thật**

Run: `Invoke-Pester tests/powershell/BackupBaseline.Tests.ps1,tests/powershell/VerifyBackup.Tests.ps1 -Output Detailed`

Expected: PASS.

Run: `pwsh -File scripts/Backup-Baseline.ps1`

Expected: có `database.sql.gz`, `uploads.tar.gz`, `site-source.tar.gz`, `environment.txt`, `SHA256SUMS` ở server và local; không thay đổi WordPress.

- [x] **Step 7: Ghi bằng chứng vào handoff và commit**

Ghi timestamp, đường dẫn local/remote, kích thước và trạng thái xác minh; không ghi DB name/user/password nếu chúng không cần cho restore operator.

```powershell
git add .gitignore scripts/Backup-Baseline.ps1 scripts/Verify-Backup.ps1 scripts/remote/backup-baseline.sh tests/powershell/BackupBaseline.Tests.ps1 tests/powershell/VerifyBackup.Tests.ps1 docs/sessions/CURRENT.md
git commit -m "ops: create verified off-server baseline backup"
```

### Task 4: Xác minh sâu và restore drill không ảnh hưởng production

**Files:**
- Modify: `scripts/Verify-Backup.ps1`
- Modify: `tests/powershell/VerifyBackup.Tests.ps1`
- Modify: `docs/runbooks/continuity-and-recovery.md`

- [x] **Step 1: Mở rộng test thất bại cho kiểm tra cấu trúc WordPress**

Thêm fixtures có SQL chung nhưng không có bảng WordPress và archive uploads không chứa entry nào. Xác nhận cả hai trường hợp đều làm verifier thoát khác `0`; fixture hợp lệ phải có ít nhất `wp_options`, `wp_posts`, `wp_postmeta` và một entry uploads an toàn.

- [x] **Step 2: Chạy test và xác nhận đỏ**

Run: `Invoke-Pester tests/powershell/VerifyBackup.Tests.ps1 -Output Detailed`

Expected: FAIL vì verifier chưa kiểm tra cấu trúc WordPress tối thiểu.

- [x] **Step 3: Cài verifier và quy trình restore tạm**

Mở rộng verifier để xác nhận dump có các bảng hậu tố `_options`, `_posts`, `_postmeta` mà không giả định prefix là `wp_`, và archive uploads có ít nhất một entry không phải path tuyệt đối/`..`. Runbook phải mô tả restore vào database tạm có tên timestamp, query số bảng/options, rồi drop đúng database tạm sau khi tên đã khớp regex `^restore_test_[0-9]{14}$`; không trỏ Nginx hoặc WordPress production vào DB tạm.

- [x] **Step 4: Chạy verifier trên baseline thật**

Run: `$BaselineBackup = Get-ChildItem -LiteralPath backups -Directory | Sort-Object LastWriteTime -Descending | Select-Object -First 1; pwsh -File scripts/Verify-Backup.ps1 -BackupPath $BaselineBackup.FullName`

Expected: tất cả checksum trùng, hai tar và gzip đọc được, SQL có cấu trúc WordPress.

- [x] **Step 5: Commit**

```powershell
git add scripts/Verify-Backup.ps1 tests/powershell/VerifyBackup.Tests.ps1 docs/runbooks/continuity-and-recovery.md
git commit -m "ops: verify backup integrity and document restore drill"
```

## Giai đoạn B — Chuẩn hóa WordPress

### Task 5: Cài WP-CLI có xác minh và kiểm tra core

**Files:**
- Create: `scripts/Install-WpCli.ps1`
- Create: `tests/powershell/InstallWpCli.Tests.ps1`
- Modify: `docs/server-inventory.md`

- [x] **Step 1: Viết test thất bại cho nguồn tải và checksum**

Mock SSH để xác nhận installer tải `wp-cli.phar` và checksum/signature từ nguồn chính thức, so khớp trước khi đặt executable, không ghi vào web root và dừng nếu xác minh lỗi.

- [x] **Step 2: Chạy test đỏ, cài tối thiểu, rồi chạy test xanh**

Run: `Invoke-Pester tests/powershell/InstallWpCli.Tests.ps1 -Output Detailed`

Expected trước cài đặt: FAIL; sau cài đặt: PASS.

- [x] **Step 3: Chạy installer sau khi xác nhận baseline còn hợp lệ**

Run: `$BaselineBackup = Get-ChildItem -LiteralPath backups -Directory | Sort-Object LastWriteTime -Descending | Select-Object -First 1; pwsh -File scripts/Verify-Backup.ps1 -BackupPath $BaselineBackup.FullName`

Expected: PASS.

Run: `pwsh -File scripts/Install-WpCli.ps1`

Expected: `wp --info` chạy được bằng PHP CLI; file cài ngoài WordPress core.

- [x] **Step 4: Kiểm tra WordPress và ghi drift**

Run qua helper SSH: `wp core version --path=/www/wwwroot/103.77.240.28 --allow-root` và `wp core verify-checksums --path=/www/wwwroot/103.77.240.28 --allow-root`.

Expected: phiên bản được WP-CLI nhận diện; checksum PASS. Nếu WordPress `7.1` không có checksum chính thức, dừng cấu hình và ghi blocker thay vì ép update/downgrade.

- [x] **Step 5: Commit**

```powershell
git add scripts/Install-WpCli.ps1 tests/powershell/InstallWpCli.Tests.ps1 docs/server-inventory.md
git commit -m "ops: install verified wp-cli and audit core"
```

### Task 6: Cài Blocksy chính thức và child theme tối thiểu

**Files:**
- Create: `theme/blocksy-child/style.css`
- Create: `theme/blocksy-child/functions.php`
- Create: `theme/blocksy-child/inc/setup.php`
- Create: `tests/php/child-theme-smoke.php`
- Create: `scripts/Deploy.ps1`
- Create: `scripts/Smoke-Test.ps1`
- Create: `tests/powershell/Deploy.Tests.ps1`
- Create: `tests/powershell/SmokeTest.Tests.ps1`

- [ ] **Step 1: Viết smoke test PHP thất bại**

Test bootstrap WordPress, xác nhận stylesheet là `blocksy-child`, template là `blocksy`, text domain `thuc-pham-thuy-trang` được khai báo và child stylesheet được enqueue với version lấy từ file.

- [ ] **Step 2: Chạy test và xác nhận đỏ**

Run trên bản staging/tạm hoặc qua deploy dry-run: `php tests/php/child-theme-smoke.php`

Expected: FAIL vì child theme chưa tồn tại.

- [ ] **Step 3: Tạo child theme tối thiểu**

`style.css` phải có WordPress theme header với `Template: blocksy`. `functions.php` chỉ require `inc/setup.php`. `setup.php` phải enqueue parent/child CSS, dùng prefix `tt_`, escape output và không chứa business data.

- [ ] **Step 4: Viết và kiểm thử deploy/smoke foundation**

Mock SSH/SCP và xác nhận `Deploy.ps1 -Component Theme -WhatIf` chỉ nhắm `wp-content/themes/blocksy-child`, luôn tạo archive rollback của child theme hiện tại nếu thư mục tồn tại, không theo symlink, và không kích hoạt theme khi copy/chown thất bại. `Smoke-Test.ps1 -Scope Theme` phải kiểm tra HTTP status, `template=blocksy`, `stylesheet=blocksy-child` và log PHP fatal mới kể từ timestamp bắt đầu test.

Run: `Invoke-Pester tests/powershell/Deploy.Tests.ps1,tests/powershell/SmokeTest.Tests.ps1 -Output Detailed`

Expected trước cài đặt: FAIL; sau khi tạo hai script: PASS và không có kết nối thật vì SSH/SCP đã được mock.

- [ ] **Step 5: Cài parent theme và deploy child theme**

Run WP-CLI: `wp theme install blocksy --path=/www/wwwroot/103.77.240.28 --allow-root` từ repository WordPress chính thức. Deploy child theme vào `wp-content/themes/blocksy-child`, đặt owner `www:www`, directory `775`, file `664`, rồi activate child theme.

- [ ] **Step 6: Chạy PHP lint, smoke test và HTTP check**

Run: `php -l theme/blocksy-child/functions.php` và `php -l theme/blocksy-child/inc/setup.php`.

Expected: no syntax errors.

Run: `pwsh -File scripts/Smoke-Test.ps1 -Scope Theme`

Expected: homepage HTTP 200, parent/child active, không có PHP fatal trong error log mới.

- [ ] **Step 7: Commit**

```powershell
git add theme/blocksy-child tests/php/child-theme-smoke.php scripts/Deploy.ps1 scripts/Smoke-Test.ps1 tests/powershell/Deploy.Tests.ps1 tests/powershell/SmokeTest.Tests.ps1
git commit -m "feat: add Blocksy child theme foundation"
```

### Task 7: Cấu hình WooCommerce, trang và URL idempotent

**Files:**
- Create: `scripts/Configure-WordPress.ps1`
- Create: `tests/powershell/ConfigureWordPress.Tests.ps1`
- Modify: `docs/server-inventory.md`

- [ ] **Step 1: Viết test thất bại cho desired state**

Mock WP-CLI; xác nhận timezone `Asia/Ho_Chi_Minh`, permalink `/%postname%/`, currency `VND`, decimals `0`, country `VN`, COD enabled, các gateway khác disabled, shop slug `products`, và trang `gioi-thieu`/`lien-he` được upsert theo slug thay vì tạo trùng.

- [ ] **Step 2: Chạy test đỏ, cài script, chạy test xanh**

Run: `Invoke-Pester tests/powershell/ConfigureWordPress.Tests.ps1 -Output Detailed`

Expected trước cài đặt: FAIL; sau cài đặt: PASS.

- [ ] **Step 3: Liệt kê nội dung mặc định trước khi xóa**

Run read-only: `wp post list --post_type=page,post --fields=ID,post_type,post_status,post_title,post_name --format=table` và `wp plugin list --format=table`.

Expected: danh sách ID cụ thể được chép vào handoff. Chỉ `Hello world`, `Sample Page` và plugin mặc định đã xác nhận mới được đưa vào lệnh xóa theo từng ID; không dùng vòng lặp xóa hàng loạt.

- [ ] **Step 4: Áp desired state và smoke test**

Run: `pwsh -File scripts/Configure-WordPress.ps1 -Apply`

Expected: chạy lần một thay đổi đúng phạm vi; chạy lần hai báo no-op; `/products`, `/gioi-thieu`, `/lien-he`, cart và checkout trả HTTP 200.

- [ ] **Step 5: Commit**

```powershell
git add scripts/Configure-WordPress.ps1 tests/powershell/ConfigureWordPress.Tests.ps1 docs/server-inventory.md docs/sessions/CURRENT.md
git commit -m "feat: configure WordPress and WooCommerce defaults"
```

## Giai đoạn C — Catalog và giao diện

### Task 8: Dữ liệu mẫu idempotent

**Files:**
- Create: `data/categories.csv`
- Create: `data/products.csv`
- Create: `scripts/Seed-Catalog.ps1`
- Create: `tests/powershell/SeedCatalog.Tests.ps1`

- [ ] **Step 1: Viết fixtures đầy đủ**

`categories.csv` chứa đúng bảy cặp tên/slug ổn định sau: `Rau củ quả tươi,rau-cu-qua-tuoi`; `Thịt heo,thit-heo`; `Thịt bò,thit-bo`; `Gia cầm,gia-cam`; `Hải sản,hai-san`; `Đồ khô,do-kho`; `Thực phẩm chế biến,thuc-pham-che-bien`. `products.csv` chứa 12 sản phẩm mẫu, mỗi dòng có `sku,name,slug,regular_price,unit,category_slug,short_description,description,image_filename,featured`; tên/giá/đơn vị phải có giá trị và nội dung phải ghi rõ là dữ liệu mẫu.

- [ ] **Step 2: Viết test thất bại cho schema và idempotency**

Test xác nhận bảy category slug duy nhất, 12 SKU/slug duy nhất, giá là số nguyên dương, unit thuộc tập `kg,gói,hộp,con,khay`, category tồn tại, image filename không thoát khỏi `data/images`. Mock WP-CLI để lần chạy thứ hai update theo SKU và không tạo product/category mới.

- [ ] **Step 3: Chạy test đỏ, cài importer, chạy test xanh**

Run: `Invoke-Pester tests/powershell/SeedCatalog.Tests.ps1 -Output Detailed`

Expected trước importer: FAIL; sau importer: PASS.

- [ ] **Step 4: Nhập catalog và xác minh**

Run: `pwsh -File scripts/Seed-Catalog.ps1 -Apply`

Expected: 7 danh mục, 12 sản phẩm publish, SKU/giá/đơn vị/category đúng; chạy lần hai không tăng count. Ảnh phải là asset mẫu có quyền sử dụng hoặc placeholder tự tạo, không sao chép từ website tham khảo.

- [ ] **Step 5: Commit**

```powershell
git add data scripts/Seed-Catalog.ps1 tests/powershell/SeedCatalog.Tests.ps1
git commit -m "feat: add reproducible sample catalog"
```

### Task 9: Dựng trang chủ, header/footer và responsive styles

**Files:**
- Create: `theme/blocksy-child/inc/home.php`
- Create: `theme/blocksy-child/inc/woocommerce.php`
- Create: `theme/blocksy-child/assets/css/site.css`
- Create: `theme/blocksy-child/assets/js/site.js`
- Modify: `theme/blocksy-child/functions.php`
- Modify: `theme/blocksy-child/inc/setup.php`
- Create: `tests/php/theme-render-smoke.php`

- [ ] **Step 1: Viết render tests thất bại**

Test xác nhận trang chủ render một H1, hero, bốn cam kết, section “Thực phẩm tươi sạch mỗi tuần”, lưới sản phẩm nổi bật; shop có category sidebar và price filter; mọi query sản phẩm có giới hạn và gọi `wp_reset_postdata()`.

- [ ] **Step 2: Chạy test và xác nhận đỏ**

Run: `php tests/php/theme-render-smoke.php`

Expected: FAIL vì hooks/sections chưa tồn tại.

- [ ] **Step 3: Cài giao diện theo component**

`home.php` cung cấp shortcode/block render có escaping; `woocommerce.php` đăng ký sidebar và hooks; CSS dùng custom properties màu xanh, grid responsive tại 1024/768/480px, focus visible, ảnh có aspect ratio; JS chỉ điều khiển menu/interaction có progressive enhancement.

- [ ] **Step 4: Deploy và kiểm tra hình ảnh**

Run: `pwsh -File scripts/Deploy.ps1 -Component Theme`

Expected: chỉ child theme thay đổi, backup phần theme trước deploy, owner/quyền giữ đúng.

Chụp và rà soát desktop 1440px cùng mobile 390px cho homepage, `/products`, category và product detail; xác nhận không overflow ngang, keyboard focus rõ, search/cart truy cập được.

- [ ] **Step 5: Chạy lint/smoke và commit**

Run: `Get-ChildItem theme/blocksy-child -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName; if ($LASTEXITCODE -ne 0) { throw "PHP lint failed: $($_.FullName)" } }; pwsh -File scripts/Smoke-Test.ps1 -Scope Catalog`.

Expected: lint PASS; các trang HTTP 200; add-to-cart hoạt động.

```powershell
git add theme/blocksy-child tests/php/theme-render-smoke.php
git commit -m "feat: build responsive storefront experience"
```

### Task 10: Nội dung Giới thiệu/Liên hệ và quản trị thân thiện

**Files:**
- Create: `data/pages/gioi-thieu.html`
- Create: `data/pages/lien-he.html`
- Create: `docs/operations/product-and-order-management.md`
- Modify: `scripts/Configure-WordPress.ps1`

- [ ] **Step 1: Viết test nội dung**

Test xác nhận không có số điện thoại/email/địa chỉ bịa đặt; nội dung liên hệ nêu rõ kênh chính thức sẽ được cập nhật; HTML không chứa script/iframe; script upsert page theo slug.

- [ ] **Step 2: Chạy test đỏ, thêm nội dung và hướng dẫn, chạy test xanh**

Run: `Invoke-Pester tests/powershell/ConfigureWordPress.Tests.ps1 -Output Detailed`

Expected trước thay đổi: FAIL; sau thay đổi: PASS.

- [ ] **Step 3: Deploy trang và kiểm tra quyền quản trị**

Upsert hai trang, đăng nhập bằng tài khoản quản trị do người dùng quản lý, thêm/sửa một sản phẩm kiểm thử, đổi trạng thái một đơn test, rồi hoàn tác dữ liệu kiểm thử theo đúng ID vừa tạo.

- [ ] **Step 4: Commit**

```powershell
git add data/pages scripts/Configure-WordPress.ps1 tests/powershell/ConfigureWordPress.Tests.ps1 docs/operations/product-and-order-management.md
git commit -m "docs: add editable pages and store operations guide"
```

## Giai đoạn D — QR, PWA và nghiệm thu

### Task 11: QR sản phẩm và tem in

**Files:**
- Create: `docs/decisions/qr-solution.md`
- Create: `plugin/tt-product-qr/tt-product-qr.php`
- Create: `plugin/tt-product-qr/src/ProductQr.php`
- Create: `plugin/tt-product-qr/assets/print.css`
- Create: `tests/php/product-qr-smoke.php`
- Modify: `scripts/Deploy.ps1`

- [ ] **Step 1: Đánh giá giải pháp miễn phí trước khi cài**

Dùng WordPress.org API và trang tác giả chính thức để ghi license, ngày cập nhật, tested-up-to, PHP requirement, active installs, cách tạo QR, dữ liệu gửi ra ngoài và khả năng in. Quyết định mặc định là plugin riêng `tt-product-qr` nếu không có plugin miễn phí nào đạt đồng thời: GPL-compatible, tương thích WordPress/PHP hiện tại, tạo QR cục bộ, không telemetry bắt buộc, không khóa product URL/print sau paywall.

- [ ] **Step 2: Viết test thất bại**

Test tạo product fixture, xác nhận payload bằng `get_permalink($product_id)`, chỉ chấp nhận scheme `http/https`, QR có accessible label, endpoint in yêu cầu capability `edit_products` và nonce hợp lệ, output không chứa secret/customer data.

- [ ] **Step 3: Chạy test đỏ, cài plugin tối thiểu, chạy test xanh**

Run: `php tests/php/product-qr-smoke.php`

Expected trước plugin: FAIL; sau plugin: PASS.

- [ ] **Step 4: Deploy và kiểm tra bằng điện thoại**

Run: `pwsh -File scripts/Deploy.ps1 -Component ProductQr`

Expected: QR hiện trên product detail và admin; quét ba sản phẩm mở đúng canonical URL; tem thử in rõ ở kích thước đã ghi trong decision. Trước khi có domain, đánh dấu tem là bản thử và không in hàng loạt.

- [ ] **Step 5: Commit**

```powershell
git add docs/decisions/qr-solution.md plugin/tt-product-qr tests/php/product-qr-smoke.php scripts/Deploy.ps1
git commit -m "feat: add product QR and label printing"
```

### Task 12: PWA với checkpoint HTTPS

**Files:**
- Create: `docs/decisions/pwa-solution.md`
- Create: `tests/powershell/Pwa.Tests.ps1`
- Modify: `scripts/Configure-WordPress.ps1`
- Modify: `scripts/Smoke-Test.ps1`

- [ ] **Step 1: Đánh giá plugin PWA miễn phí từ nguồn chính thức**

Ghi license, tested-up-to, PHP requirement, update date, service-worker scope, offline behavior và uninstall cleanup. Chỉ cài từ WordPress.org; nếu site vẫn HTTP/IP, cấu hình manifest/icon nhưng ghi trạng thái “chờ HTTPS”, không tuyên bố installable.

- [ ] **Step 2: Viết test thất bại**

Test xác nhận manifest trả JSON hợp lệ, `name`, `short_name`, `start_url`, `display`, theme/background colors và icon 192/512 cùng origin; service worker không cache admin, cart, checkout, account hoặc request có nonce.

- [ ] **Step 3: Cài/cấu hình plugin đã ghi trong decision và chạy test**

Run: `Invoke-Pester tests/powershell/Pwa.Tests.ps1 -Output Detailed`

Expected: manifest PASS; khi chưa HTTPS, installability test báo SKIP với lý do rõ ràng, không FAIL giả.

- [ ] **Step 4: Commit**

```powershell
git add docs/decisions/pwa-solution.md tests/powershell/Pwa.Tests.ps1 scripts/Configure-WordPress.ps1 scripts/Smoke-Test.ps1
git commit -m "feat: configure PWA with HTTPS readiness checks"
```

### Task 13: Luồng mua COD và regression suite

**Files:**
- Modify: `scripts/Smoke-Test.ps1`
- Modify: `tests/powershell/SmokeTest.Tests.ps1`
- Create: `tests/e2e/cod-checkout.md`
- Create: `docs/operations/acceptance-report.md`

- [ ] **Step 1: Viết checklist test có dữ liệu cụ thể**

Checkout test dùng một SKU mẫu, số lượng 1, địa chỉ kiểm thử rõ ràng, COD; ghi order ID vừa tạo. Xác nhận subtotal/total VNĐ, validation trường bắt buộc, gateway COD duy nhất, order xuất hiện trong Admin và không lưu thông tin kiểm thử lâu hơn cần thiết.

- [ ] **Step 2: Chạy regression tự động**

Run: `pwsh -File scripts/Smoke-Test.ps1 -Scope All`

Expected: homepage/shop/category/product/cart/checkout HTTP 200; catalog count 12; child theme/plugin active; QR payload đúng; manifest có cấu trúc; không có PHP fatal mới.

- [ ] **Step 3: Chạy checkout thủ công desktop/mobile**

Tạo đúng một order COD test, xác nhận trong Admin, chuyển trạng thái theo quy trình, chụp bằng chứng không chứa dữ liệu nhạy cảm, rồi xóa/anonymize đúng order ID test nếu chính sách cho phép.

- [ ] **Step 4: Ghi acceptance report và commit**

```powershell
git add scripts/Smoke-Test.ps1 tests/powershell/SmokeTest.Tests.ps1 tests/e2e/cod-checkout.md docs/operations/acceptance-report.md
git commit -m "test: verify storefront and COD checkout"
```

### Task 14: Domain, HTTPS, canonical URL và nghiệm thu cuối

**Files:**
- Create: `docs/runbooks/domain-https-cutover.md`
- Modify: `scripts/Smoke-Test.ps1`
- Modify: `docs/operations/acceptance-report.md`
- Modify: `docs/sessions/CURRENT.md`
- Modify: `config/local.example.ps1`

- [ ] **Step 1: Ghi preconditions không gây thay đổi**

Runbook yêu cầu domain thuộc quyền người dùng, DNS đã trỏ đúng, quyền aaPanel/registrar hoặc Cloudflare, backup mới đã xác minh và maintenance window. Nếu thiếu bất kỳ điều kiện nào, dừng ở đây; website IP vẫn vận hành và PWA/QR chỉ ở trạng thái thử.

- [ ] **Step 2: Backup ngay trước cutover**

Run: `pwsh -File scripts/Backup-Baseline.ps1 -Label pre-domain-cutover`

Expected: backup local/server mới có checksum PASS.

- [ ] **Step 3: Cutover tuần tự**

Thêm domain trong aaPanel, cấp Let’s Encrypt, xác minh certificate, đặt URL cũ và URL đã duyệt vào `$ProjectOldBaseUrl`/`$ProjectBaseUrl` trong `config/local.ps1`, dùng `wp search-replace "$ProjectOldBaseUrl" "$ProjectBaseUrl" --all-tables-with-prefix --precise --dry-run` trước, xem count, rồi chạy lại không `--dry-run`; cập nhật home/siteurl, flush rewrite/cache và chỉ sau đó bật redirect HTTPS. Cloudflare dùng Full (strict), không Flexible. `config/local.example.ps1` khai báo hai biến rỗng để script có thể kiểm tra fail-closed; file local thật vẫn bị ignore.

- [ ] **Step 4: Nghiệm thu HTTPS/PWA/QR**

Run: `. .\config\local.ps1; pwsh -File scripts/Smoke-Test.ps1 -Scope All -BaseUrl $ProjectBaseUrl`

Expected: không mixed content/redirect loop; checkout COD pass; manifest/service worker cùng origin; browser báo PWA installable; quét QR mở canonical HTTPS URL. Chỉ QR tạo sau cutover mới được duyệt để in chính thức.

- [ ] **Step 5: Cập nhật handoff và commit**

Ghi backup mới nhất, domain, certificate expiry, kết quả kiểm thử, rollback path, vấn đề còn lại và commit hash trước phiên kế tiếp.

```powershell
git add config/local.example.ps1 docs/runbooks/domain-https-cutover.md scripts/Smoke-Test.ps1 docs/operations/acceptance-report.md docs/sessions/CURRENT.md
git commit -m "docs: complete production cutover handoff"
```

## Cổng kiểm soát bắt buộc

- Không bắt đầu Task 5 nếu Task 3–4 chưa có backup local ngoài VPS và checksum PASS.
- Không xóa page/post/plugin nếu chưa liệt kê ID/slug cụ thể trong handoff.
- Không deploy file ngoài `wp-content/themes/blocksy-child` và `wp-content/plugins/tt-product-qr`, trừ WP-CLI được cài vào vị trí hệ thống đã ghi trong inventory.
- Không tuyên bố PWA hoàn chỉnh hoặc in tem QR chính thức trước Task 14.
- Sau mỗi task: chạy test liên quan, `git status --short`, rà secret, cập nhật `docs/sessions/CURRENT.md`, rồi commit nhỏ với thông điệp đã nêu.

## Ma trận bao phủ yêu cầu

- Backup/restore/continuity: Tasks 1–5, 14.
- Blocksy child theme và responsive UI: Tasks 6, 9.
- Cấu hình WordPress/WooCommerce, `/products`, VNĐ, COD: Tasks 7, 13.
- Bảy danh mục và 12 sản phẩm mẫu: Task 8.
- Trang chủ, tìm kiếm, category/filter, product detail, cart/checkout: Tasks 9, 13.
- Giới thiệu, Liên hệ, quản trị sản phẩm/đơn hàng: Task 10.
- QR sản phẩm/tem nhiệt: Task 11 và nghiệm thu lại ở Task 14.
- PWA: Task 12 và nghiệm thu HTTPS ở Task 14.
- Domain/DNS/SSL/Cloudflare: Task 14, chỉ khi người dùng cung cấp quyền cần thiết.
