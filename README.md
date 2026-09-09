# Thực phẩm Thủy Trang - WordPress/WooCommerce

Repository này là nguồn chuẩn để xây dựng, vận hành và khôi phục website bán thực phẩm trên WordPress/WooCommerce.

## Nguyên tắc lưu trữ

- Git lưu code tùy biến, script tự động hóa, dữ liệu mẫu không nhạy cảm và tài liệu.
- Server chạy WordPress core, plugin, database và uploads.
- Database, uploads và `wp-config.php` phải được backup riêng ngoài VPS; không commit vào Git.
- Private key, mật khẩu và token không được ghi vào repository.

## Bắt đầu một phiên làm việc mới

1. Đọc [AGENTS.md](AGENTS.md).
2. Đọc [docs/sessions/CURRENT.md](docs/sessions/CURRENT.md).
3. Đọc [docs/server-inventory.md](docs/server-inventory.md).
4. Đọc thiết kế mới nhất trong `docs/superpowers/specs/`.
5. Chạy kiểm tra chỉ đọc trước khi thay đổi server.
6. Cập nhật `docs/sessions/CURRENT.md` trước khi kết thúc phiên.

## Cấu trúc

```text
config/                   Cấu hình mẫu và cấu hình riêng của máy (bị Git ignore)
data/                     Dữ liệu danh mục/sản phẩm mẫu không nhạy cảm
docs/
  runbooks/               Quy trình vận hành, backup và khôi phục
  sessions/               Bộ nhớ chuyển giao giữa các phiên
  superpowers/specs/      Thiết kế đã được duyệt
scripts/                  Script setup, backup, deploy và kiểm thử
theme/blocksy-child/      Child theme tùy biến của website
```

## Server hiện tại

- IP: `103.77.240.28`
- SSH: user `root`, port `2222`
- Web root: `/www/wwwroot/103.77.240.28`
- URL tạm: `http://103.77.240.28`

Chi tiết và bằng chứng khảo sát nằm trong [docs/server-inventory.md](docs/server-inventory.md).
