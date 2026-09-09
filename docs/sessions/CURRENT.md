# Trạng thái phiên hiện tại

Cập nhật: 2026-09-09, múi giờ Asia/Ho_Chi_Minh.

## Sau khi khởi động lại máy

- Mở Codex với workspace tuyệt đối: `E:\Projects\thuc-pham-thuy-trang`.
- Gửi yêu cầu: `Đọc AGENTS.md và docs/sessions/CURRENT.md, kiểm tra Git rồi tiếp tục đúng bước tiếp theo.`
- Repository ở ổ E là bản làm việc chính. Bản cũ ở ổ C chỉ là dự phòng và không được chỉnh song song.
- File `config/local.ps1` ở bản ổ E chứa metadata kết nối riêng của máy và bị Git ignore.
- Private key vẫn nằm ngoài repository tại đường dẫn được tham chiếu trong `config/local.ps1`.

## Mục tiêu đang thực hiện

Đang thực thi liên tục implementation plan 14 task để hoàn thiện và deploy bản website chạy qua IP hiện tại. Mọi thay đổi VPS vẫn bị chặn cho đến khi baseline backup ngoài VPS được tạo và xác minh.

## Phiên đang làm

- Đã đọc lại `AGENTS.md`, handoff, server inventory, project brief, runbook và thiết kế đã duyệt.
- Git ở nhánh `main`, commit gần nhất trước khi hoàn thiện plan là `d87401e`; các thay đổi tài liệu dang dở đã được rà soát và tiếp tục, không bị ghi đè.
- Đã nạp `config/local.ps1` mà không in secret; mọi khảo sát server tiếp theo vẫn phải chỉ đọc cho đến khi baseline backup được tạo và xác minh.
- Đã hoàn thiện và tự rà soát `docs/superpowers/plans/2026-09-09-thuc-pham-woocommerce-implementation.md`; plan gồm 14 task, có cổng backup bắt buộc trước thay đổi WordPress.
- Phiên hiện tại tiếp tục trực tiếp trên repository chính theo yêu cầu người dùng, dùng TDD và review theo từng task; không tạo/xóa worktree và không ghi đè thay đổi handoff sẵn có.

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
- Chuẩn bị sao chép nguyên repository và Git history sang `E:\Projects\thuc-pham-thuy-trang` để dùng sau khi restart.
- Viết implementation plan chi tiết từ brief và kiến trúc đã duyệt; sửa dependency để verifier được tạo trước khi backup thật và deploy/smoke foundation được tạo trước lần sử dụng đầu tiên.

## Hiện trạng quan trọng

- Server đang chạy website trực tiếp từ `/www/wwwroot/103.77.240.28`.
- Chưa có WP-CLI, Git repo trên server hoặc backup site/database quan sát được.
- Website hiện gần như trống: 0 sản phẩm, 0 đơn hàng, theme mặc định.
- Chưa thực hiện thay đổi nào trên server trong các phiên khảo sát.

## Bước tiếp theo

1. Chọn cách thực thi plan: subagent-driven hoặc inline trong phiên hiện tại.
2. Thực hiện Task 1: viết test Pester đỏ cho `Import-ProjectConfig`, sau đó tạo `scripts/lib/Project.Common.ps1` và chạy test xanh.
3. Thực hiện Task 2: tự động hóa preflight/inventory chỉ đọc.
4. Thực hiện Task 3–4: tạo baseline backup database + uploads/source, tải bản ngoài VPS và xác minh checksum/cấu trúc.
5. Chỉ sau khi backup đạt mới cài WP-CLI và bắt đầu thay đổi WordPress theo Task 5 trở đi.

## Việc chưa chốt

- Cần xác minh lại quyền ghi `.git` bằng lần commit tài liệu đầu phiên; blocker cũ được giữ trong lịch sử nếu quyền vẫn chưa được cấp.
- Logo, thông tin liên hệ, banner và ảnh sản phẩm chính thức.
- Mức độ mô phỏng chi tiết giao diện website mẫu.
- Domain cuối cùng và tài khoản Cloudflare/registrar.
- Plugin QR miễn phí phù hợp WordPress/PHP hiện tại; cần khảo sát trước khi cài.

## Quy tắc kết thúc mỗi phiên

Cập nhật file này với: thay đổi đã làm, kiểm thử và kết quả, backup/rollback liên quan, vấn đề còn lại, bước tiếp theo và hash commit mới nhất.
