# Thiết kế dự án website Thực phẩm Thủy Trang

Ngày: 2026-09-09
Trạng thái: Đã được người dùng duyệt phương án kiến trúc số 3; chờ người dùng rà soát tài liệu viết.

## 1. Mục tiêu thiết kế

Hoàn thiện website WordPress/WooCommerce bán thực phẩm trên VPS hiện có, có giao diện tham khảo website mẫu, checkout COD, QR sản phẩm và PWA. Hệ thống phải có khả năng tiếp tục qua nhiều phiên làm việc và phục hồi khi VPS hỏng.

## 2. Phương án đã chọn

Sử dụng mô hình hybrid:

- Repository local là nguồn chuẩn cho code tùy biến, automation và tài liệu.
- VPS là runtime cho WordPress, WooCommerce, database và uploads.
- Backup database/uploads nằm ngoài Git và có ít nhất một bản ngoài VPS.
- Deploy bằng script có bước kiểm tra, backup và rollback thay vì chỉnh sửa tùy tiện trên server.

Không chọn server-only vì mất VPS có thể mất toàn bộ. Không commit toàn bộ WordPress vì core/plugin bên thứ ba gây nhiễu, chứa file sinh tự động và vẫn không bảo vệ database/uploads.

## 3. Kiến trúc thành phần

### 3.1 Repository local

- `theme/blocksy-child`: CSS, PHP hooks, template override tối thiểu và assets do dự án sở hữu.
- `scripts`: setup, inventory, backup, deploy, smoke test và restore.
- `config`: file mẫu không chứa secret; file máy cá nhân bị Git ignore.
- `data`: danh mục/sản phẩm mẫu ở định dạng có thể nhập lặp lại.
- `docs`: yêu cầu, inventory, runbook, thiết kế và handoff phiên.

### 3.2 Runtime WordPress

- WordPress core và plugin bên thứ ba được cài từ nguồn chính thức.
- Blocksy là parent theme; child theme chứa mọi tùy biến thuộc dự án.
- WooCommerce quản lý catalog, cart, checkout COD và order.
- Plugin QR/PWA chỉ được chọn sau khi kiểm tra nguồn, tương thích và phạm vi miễn phí.

### 3.3 Dữ liệu bền vững

- MariaDB: pages, products, taxonomy, settings, users, orders.
- Uploads: ảnh sản phẩm, logo, banner và media.
- Cả hai được backup riêng; Git không thay thế chúng.

## 4. Luồng thay đổi và triển khai

1. Phiên mới đọc `AGENTS.md` và `docs/sessions/CURRENT.md`.
2. Chạy inventory chỉ đọc, so sánh với tài liệu.
3. Tạo/kiểm tra backup trước thay đổi quan trọng.
4. Phát triển code trong repository và commit.
5. Deploy đúng phần thay đổi lên server, giữ owner/group/quyền phù hợp.
6. Chạy smoke tests và kiểm tra luồng liên quan.
7. Nếu lỗi, rollback từ artifact/bản backup vừa tạo.
8. Cập nhật inventory/handoff và commit tài liệu.

## 5. Giao diện và chức năng

- Thiết kế responsive, màu xanh lá, tham khảo cấu trúc website mẫu nhưng không sao chép source/nhãn hiệu không được phép.
- Trang chủ: header/search/cart, hero/banner, bốn cam kết, khối thực phẩm tươi mỗi tuần, sản phẩm nổi bật và footer.
- Cửa hàng `/products`: sidebar danh mục/lọc giá và lưới sản phẩm.
- Chi tiết sản phẩm: ảnh, giá VNĐ, đơn vị, mô tả, danh mục, thêm giỏ và QR.
- Giỏ hàng/checkout: COD, validation dữ liệu khách và tạo đơn trong Admin.
- Giới thiệu/Liên hệ: nội dung thật được thay thế khi khách cung cấp.
- PWA chỉ được nghiệm thu hoàn chỉnh sau HTTPS/domain.

## 6. Xử lý lỗi và an toàn

- Mọi script dừng khi bước quan trọng thất bại và không tiếp tục deploy một phần.
- Không log secret hoặc nội dung `wp-config.php`.
- Lệnh xóa phải liệt kê mục tiêu cụ thể; không xóa hàng loạt theo ví dụ nguy hiểm trong brief.
- Backup có checksum; restore được diễn tập ở môi trường tạm khi có thể.
- Không chỉnh WordPress core; update core/plugin không được ghi đè child theme.
- Giữ SSH port, aaPanel và các website khác nguyên trạng.

## 7. Kiểm thử

- Kiểm tra trang chủ/cửa hàng/sản phẩm trên desktop và mobile.
- Kiểm tra search, filter, add-to-cart, cart và checkout COD.
- Xác nhận đơn xuất hiện trong Admin.
- Quét QR trên điện thoại và xác nhận đúng canonical product URL.
- Kiểm tra PWA manifest/service worker/installability sau HTTPS.
- Kiểm tra admin có thể thêm/sửa sản phẩm theo tài liệu bàn giao.
- Chạy restore drill tối thiểu cho database và kiểm tra archive uploads đọc được.

## 8. Khả năng tiếp tục qua phiên

`docs/sessions/CURRENT.md` là handoff sống và được cập nhật cuối mỗi phiên. `AGENTS.md` bắt buộc phiên mới đọc handoff, inventory, design và Git history trước khi hành động. Các quyết định dài hạn nằm trong design/runbook thay vì chỉ tồn tại trong hội thoại.

## 9. Ranh giới phạm vi

- Domain, DNS, SSL, Cloudflare và QR cuối cùng thực hiện sau khi người dùng cung cấp domain/quyền truy cập.
- Nội dung/ảnh chính thức phụ thuộc đầu vào khách hàng; trước đó dùng dữ liệu mẫu rõ ràng.
- Repository local hiện là bản bảo vệ code; backup ngoài VPS mới là bản bảo vệ dữ liệu đầy đủ.

## 10. Tiêu chí nghiệm thu kiến trúc

- Repository có Git history và không chứa secret/backup nhạy cảm.
- Có inventory, brief, thiết kế, runbook phục hồi và handoff hiện tại.
- Một phiên mới có thể xác định server, web root, trạng thái và bước tiếp theo mà không cần đọc hội thoại cũ.
- Trước triển khai chức năng phải có implementation plan và baseline backup đã xác minh.
