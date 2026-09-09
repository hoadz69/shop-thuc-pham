# Runbook duy trì và khôi phục dự án

## Mục tiêu phục hồi

Một website WordPress chỉ phục hồi đầy đủ khi có đủ bốn thành phần:

1. Repository: child theme, plugin tùy biến, scripts, dữ liệu mẫu và tài liệu.
2. Database dump: trang, sản phẩm, cấu hình, người dùng và đơn hàng.
3. `wp-content/uploads`: toàn bộ ảnh/tệp người dùng tải lên.
4. Manifest môi trường: phiên bản WordPress, PHP, plugin/theme, URL, cron và cấu hình cần thiết.

`wp-config.php` và secret được sao lưu mã hóa hoặc quản lý riêng; không lưu trong Git.

## Chiến lược backup

- Trước mọi thay đổi lớn: tạo snapshot database và archive uploads/source liên quan.
- Tự động hằng ngày: database, giữ tối thiểu 7 bản gần nhất.
- Tự động hằng tuần: database + uploads, giữ tối thiểu 4 bản.
- Có ít nhất một bản ngoài VPS: máy local hoặc object storage đáng tin cậy.
- Backup chứa dữ liệu khách hàng phải được mã hóa và hạn chế quyền truy cập.
- Mỗi lần backup phải có checksum, timestamp UTC/Vietnam, kích thước và kết quả lệnh.
- Định kỳ thử restore vào môi trường tạm; “có file backup” không đồng nghĩa “khôi phục được”.

## Quy trình trước thay đổi

1. Ghi mục tiêu vào `docs/sessions/CURRENT.md`.
2. Kiểm tra dung lượng ổ đĩa.
3. Dump database nhất quán.
4. Archive `wp-content/uploads` và phần code sẽ thay đổi.
5. Tải bản backup ra ngoài VPS.
6. Kiểm tra checksum ở cả server và nơi lưu ngoài.
7. Chỉ bắt đầu thay đổi khi các bước trên thành công.

## Khôi phục mức cao

1. Dựng Ubuntu/LNMP tương thích hoặc tạo site mới trong aaPanel.
2. Cài WordPress core đúng phiên bản sạch.
3. Checkout repository và deploy child theme/plugin tùy biến.
4. Khôi phục uploads.
5. Tạo database/user mới và import dump.
6. Cấu hình `wp-config.php` bằng secret được cấp riêng.
7. Nếu URL thay đổi, dùng WP-CLI search-replace hỗ trợ serialized data.
8. Cập nhật permalink, xóa cache và kiểm tra quyền file.
9. Kiểm thử trang chủ, sản phẩm, ảnh, đăng nhập admin, COD, đơn hàng, QR và PWA.
10. Chỉ chuyển DNS sau khi kiểm thử đạt.

## Mất phiên Codex nhưng máy và repository còn

Phiên mới thực hiện theo thứ tự:

1. Mở repository và đọc `AGENTS.md`.
2. Đọc `docs/sessions/CURRENT.md` và commit gần nhất.
3. Chạy `git status` và `git log -5 --oneline`.
4. Đọc `config/local.ps1` nếu tồn tại; không in nội dung secret ra hội thoại/log.
5. Kết nối chỉ đọc và so sánh server với `docs/server-inventory.md`.
6. Tiếp tục đúng mục “Bước tiếp theo”, cập nhật handoff trước khi kết thúc.

## Mất cả VPS

- Tạo VPS mới.
- Dùng repository để khôi phục code và quy trình.
- Dùng bản backup ngoài VPS để khôi phục database/uploads.
- Gắn domain, SSL và chạy bộ kiểm thử bàn giao.

Nếu chỉ có Git mà không có database/uploads ngoài VPS, dữ liệu phát sinh sau triển khai vẫn có thể mất.
