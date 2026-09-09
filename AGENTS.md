# Hướng dẫn bắt buộc cho mọi phiên Codex

## Mục tiêu

Hoàn thiện website bán thực phẩm bằng WordPress + WooCommerce theo `docs/project-brief.md`, đồng thời bảo đảm có thể phục hồi khi VPS hỏng hoặc phiên làm việc bị mất.

## Trước khi làm

1. Đọc `docs/sessions/CURRENT.md` để biết trạng thái và bước tiếp theo.
2. Đọc `docs/server-inventory.md` và thiết kế mới nhất trong `docs/superpowers/specs/`.
3. Kiểm tra `git status`; không ghi đè thay đổi chưa rõ nguồn gốc.
4. Nạp thông tin kết nối từ `config/local.ps1` nếu file tồn tại. File này không được commit.
5. Mọi thao tác khảo sát phải chỉ đọc. Trước thay đổi có rủi ro, tạo và xác minh backup.

## Quy tắc an toàn

- Không commit private key, mật khẩu, token, `wp-config.php`, database dump hoặc dữ liệu khách hàng.
- Không đổi SSH port `2222`, không tắt aaPanel và không phá cấu trúc `/www/wwwroot`.
- Không dùng theme/plugin null, crack hoặc trả phí khi chưa được người dùng chấp thuận.
- Không sửa WordPress core (`wp-admin`, `wp-includes`). Tùy biến phải nằm trong child theme hoặc plugin riêng.
- Không chạy lệnh xóa hàng loạt page/post trong brief. Chỉ xóa đúng nội dung mặc định sau khi đã liệt kê ID và xác nhận mục tiêu.
- Không coi Git là backup đầy đủ. Khả năng phục hồi cần cả code + database + uploads + manifest môi trường.
- Không đưa backup có dữ liệu nhạy cảm lên kho công khai.

## Quy trình thay đổi

1. Ghi hiện trạng và mục tiêu phiên vào `docs/sessions/CURRENT.md`.
2. Backup trước thay đổi quan trọng.
3. Thực hiện thay đổi nhỏ, có thể kiểm chứng và có đường rollback.
4. Kiểm thử giao diện, WooCommerce, checkout COD, QR và PWA theo phạm vi liên quan.
5. Commit code/tài liệu bằng thông điệp rõ ràng.
6. Cập nhật `docs/sessions/CURRENT.md`: đã làm, bằng chứng, vấn đề còn lại, bước tiếp theo và commit mới nhất.

## Nguồn sự thật

- Yêu cầu: `docs/project-brief.md`
- Kiến trúc đã duyệt: `docs/superpowers/specs/2026-09-09-thuc-pham-woocommerce-design.md`
- Hiện trạng hạ tầng: `docs/server-inventory.md`
- Trạng thái phiên: `docs/sessions/CURRENT.md`
- Khôi phục/continuity: `docs/runbooks/continuity-and-recovery.md`
