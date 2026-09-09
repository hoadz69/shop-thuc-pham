# Runbook chuyển domain và HTTPS

Runbook này chỉ được chạy khi chủ website đã cung cấp domain và quyền quản lý DNS. Website IP phải tiếp tục hoạt động nếu bất kỳ precondition nào còn thiếu.

## Cổng bắt buộc

- Domain thuộc quyền kiểm soát của người dùng; đã chốt hostname chính (`example.com` hay `www.example.com`).
- Có quyền DNS tại registrar/Cloudflare và quyền aaPanel cần thiết; A/AAAA record đã được rà soát.
- Có maintenance window được người dùng chấp thuận và người thực hiện có thể rollback ngay.
- `config/local.ps1` có `$ProjectOldBaseUrl` và `$ProjectBaseUrl` là hai HTTP(S) URL hợp lệ, khác nhau; file vẫn bị Git ignore.
- Preflight kết nối PASS và một backup `pre-domain-cutover` mới đã tải ra ngoài VPS, checksum/cấu trúc PASS.
- TTL DNS đã được hạ trước nếu cần; không dùng Cloudflare Flexible. Đích cuối dùng Full (strict).

Thiếu một mục bất kỳ: dừng. Không đổi `home`, `siteurl`, database hay Nginx. QR/PWA giữ trạng thái thử trên IP.

## Chuẩn bị và backup

```powershell
pwsh -File scripts/Test-Connection.ps1
pwsh -File scripts/Backup-Baseline.ps1 -Label pre-domain-cutover
$backup = Get-ChildItem backups -Directory | Sort-Object LastWriteTime -Descending | Select-Object -First 1
pwsh -File scripts/Verify-Backup.ps1 -BackupPath $backup.FullName
```

Ghi lại đường dẫn backup local/remote và checksum trước khi tiếp tục.

## Cutover tuần tự

1. Thêm domain vào cùng site trong aaPanel; không tạo web root mới và không thay SSH port/aaPanel.
2. Trỏ DNS vào VPS, chờ resolver công cộng trả đúng IP.
3. Cấp Let’s Encrypt trong aaPanel; kiểm tra hostname/SAN, chain và ngày hết hạn trước redirect.
4. Nạp cấu hình local, chạy search-replace dry-run trước:

```powershell
. .\config\local.ps1
# Chạy qua Invoke-ProjectSsh với argument đã escape; không chép credential vào command/log.
wp search-replace "$ProjectOldBaseUrl" "$ProjectBaseUrl" --all-tables-with-prefix --precise --dry-run
```

5. Lưu số replacement, xem các bảng bị tác động. Chỉ khi hợp lý mới chạy lại bỏ `--dry-run`.
6. Cập nhật `home`/`siteurl`, flush rewrite/cache; xác minh URL mới trước khi bật redirect HTTP → HTTPS và old host → canonical host.
7. Nếu dùng Cloudflare, đặt SSL/TLS Full (strict), không Flexible; bật proxy sau khi origin certificate hoạt động trực tiếp.

## Nghiệm thu

```powershell
. .\config\local.ps1
pwsh -File scripts/Smoke-Test.ps1 -Scope All -BaseUrl $ProjectBaseUrl
```

Kiểm tra thêm bằng browser desktop/mobile: certificate hợp lệ, không mixed content/redirect loop, checkout COD, manifest/worker cùng origin, service worker registered và PWA installable. Quét ít nhất ba QR; chỉ QR canonical HTTPS tạo sau cutover mới được duyệt in chính thức.

## Rollback

Nếu URL mới, certificate hoặc checkout lỗi:

1. Tắt redirect/proxy vừa bật để khôi phục truy cập origin IP.
2. Dùng cặp URL đảo chiều với `wp search-replace ... --dry-run`, xem count rồi mới chạy thật; khôi phục `home`/`siteurl` cũ.
3. Nếu dữ liệu không nhất quán, dừng ghi đơn và phục hồi từ backup `pre-domain-cutover` theo `continuity-and-recovery.md`.
4. Flush rewrite/cache, chạy smoke trên `$ProjectOldBaseUrl`, ghi lỗi và đường dẫn backup vào `docs/sessions/CURRENT.md`.

Không xóa backup cutover, web root cũ hoặc cấu hình vhost để “sửa nhanh”.
