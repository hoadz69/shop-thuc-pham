# Trạng thái phiên hiện tại

Cập nhật: 2026-09-09, múi giờ Asia/Ho_Chi_Minh.

## Mục tiêu đang thực hiện

Thiết lập nguồn sự thật local/Git và tài liệu chuyển giao trước khi thay đổi WordPress trên VPS.

## Quyết định đã được người dùng duyệt

- Chọn phương án 3: project local + Git + backup ngoài VPS + deploy có kiểm soát.
- Codex được phép tự kết nối và khảo sát server bằng SSH key do người dùng cung cấp.
- Phải viết đủ tri thức và nhật ký để phiên Codex khác tiếp tục nếu phiên hiện tại bị mất.

## Đã hoàn thành

- Đọc project brief gốc.
- Đối chiếu website mẫu ở mức nội dung/bố cục.
- Xác nhận private key là PuTTY PPK v3, loại `ssh-ed25519`.
- Ghim host fingerprint và SSH thành công ở chế độ chỉ đọc.
- Khảo sát filesystem, WordPress, theme/plugin, nội dung cơ bản và backup hiện có.
- Tạo cấu trúc repository local và bộ tài liệu nền tảng.

## Hiện trạng quan trọng

- Server đang chạy website trực tiếp từ `/www/wwwroot/103.77.240.28`.
- Chưa có WP-CLI, Git repo trên server hoặc backup site/database quan sát được.
- Website hiện gần như trống: 0 sản phẩm, 0 đơn hàng, theme mặc định.
- Chưa thực hiện thay đổi nào trên server trong các phiên khảo sát.

## Bước tiếp theo sau khi người dùng duyệt tài liệu

1. Viết implementation plan chi tiết.
2. Tạo baseline backup database + uploads/source và tải một bản về ngoài VPS.
3. Xác minh checksum và khả năng đọc backup.
4. Cài WP-CLI, kiểm tra WordPress checksum/phiên bản.
5. Bắt đầu cấu hình theme/plugin theo plan đã duyệt.

## Việc chưa chốt

- Logo, thông tin liên hệ, banner và ảnh sản phẩm chính thức.
- Mức độ mô phỏng chi tiết giao diện website mẫu.
- Domain cuối cùng và tài khoản Cloudflare/registrar.
- Plugin QR miễn phí phù hợp WordPress/PHP hiện tại; cần khảo sát trước khi cài.

## Quy tắc kết thúc mỗi phiên

Cập nhật file này với: thay đổi đã làm, kiểm thử và kết quả, backup/rollback liên quan, vấn đề còn lại, bước tiếp theo và hash commit mới nhất.
