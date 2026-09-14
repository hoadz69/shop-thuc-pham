# Sự cố WordPress ngày 2026-09-14

## Tóm tắt

Trang chủ bị cắt giữa chừng và trả lỗi PHP fatal vì WooCommerce đã bị xóa, trong khi child theme vẫn gọi `wc_get_template_part()`. Điều tra tiếp xác nhận tài khoản quản trị đã bị truy cập trái phép; đây là sự cố xâm nhập WordPress, không phải lỗi CSS/cache đơn thuần.

Nguyên nhân truy cập ban đầu chưa thể kết luận tuyệt đối từ access log. Dấu hiệu phù hợp nhất là thông tin đăng nhập administrator bị lộ hoặc quá yếu, đặc biệt vì tài khoản test tạm tồn tại tại thời điểm xảy ra sự cố.

## Dấu hiệu đã xác nhận

- `wp-file-manager` 8.0.4 được cài/kích hoạt trái với inventory dự án.
- WooCommerce, `tt-product-qr` và `tt-pwa` bị xóa khỏi web root.
- Administrator lạ `gujzamuw` (ID 3) được tạo lúc `2026-09-14 01:16:17` UTC.
- Nhiều PHP webshell xuất hiện trong `wp-admin`, `wp-includes` và thư mục gốc child theme.
- `wp core verify-checksums` thất bại trước xử lý.
- Nginx ghi fatal lặp lại tại `blocksy-child/inc/home.php:65` do thiếu hàm WooCommerce.

Danh sách file và nội dung đầy đủ được bảo toàn trong backup forensic; không chép payload độc hại vào Git.

## Backup và quarantine

- Full backup trước xử lý: `/www/backup/site/thuc-pham-thuy-trang/20260914T041509Z`.
- Bản local ignored: `backups/20260914T041509Z-pre-incident-cleanup`.
- Kích thước local: `70.846.118` byte; checksum, gzip/tar và cấu trúc database/uploads đều `PASS`.
- IOC đã được chuyển khỏi web root vào `/www/backup/site/thuc-pham-thuy-trang/incident/20260914T041509Z` (quyền `700`).
- Backup cấu hình Nginx trước hardening có SHA-256 `9360263216cdc830ec7fbf16e75d4de4c07fcf7764021110452b46b026468644` và đã xác minh local/remote.

## Khắc phục đã thực hiện

1. Bật maintenance mode và cô lập đúng các plugin/file đã liệt kê.
2. Cài đè WordPress 7.1, Blocksy 2.1.56 và WooCommerce 11.1.0 từ WordPress.org.
3. Deploy lại child theme, `tt-product-qr` và `tt-pwa` từ repository.
4. Xóa administrator lạ ID 3 và tài khoản test tạm ID 2; cả hai không có post để reassign.
5. Đổi mật khẩu administrator chính, shuffle toàn bộ WordPress salts và vô hiệu hóa mọi session cũ.
6. Bật `DISALLOW_FILE_EDIT` và `FORCE_SSL_ADMIN` trong `wp-config.php`.
7. Chuyển owner của core/theme/plugin sang `root:www`, chỉ giữ uploads writable bởi `www`.
8. Chặn thực thi `php`, `phpN`, `phtml`, `phar` dưới `/wp-content/uploads/` bằng Nginx; `nginx -t` PASS trước reload.
9. Thêm guard trong homepage để không fatal nếu WooCommerce bị thiếu/tắt trong tương lai.

## Bằng chứng sau phục hồi

- WordPress core checksum: `PASS`.
- WooCommerce checksum: `PASS`.
- Plugin active chỉ gồm WooCommerce, `tt-product-qr`, `tt-pwa`.
- Administrator chỉ còn `qtri_thuytrang` (ID 1).
- Không có PHP executable trong uploads; request probe `.php` dưới uploads trả `403`.
- Database không còn chuỗi IOC đã biết; không phát hiện cron lạ liên quan IOC.
- Pester `64/64`, ba PHP smoke test `PASS`, smoke `All` `PASS`.
- Playwright desktop `1440px` và mobile `390px`: HTTP 200, đủ 4 card nổi bật/footer, không có critical-error text; mobile `scrollWidth = viewportWidth = 390`.

## Việc chủ website phải làm

- Dùng mật khẩu mới được bàn giao riêng và đổi lại một lần nữa sau khi đăng nhập.
- Không tái sử dụng mật khẩu cũ; bật 2FA nếu bổ sung giải pháp phù hợp.
- Đổi mật khẩu các tài khoản khác nếu từng dùng chung mật khẩu với WordPress (email, aaPanel, registrar).
- Không cài plugin File Manager trong WordPress; tiếp tục deploy code qua repository/SSH có backup.

