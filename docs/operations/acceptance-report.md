# Báo cáo nghiệm thu storefront

Cập nhật: 2026-09-09. Base URL hiện tại: `http://103.77.240.28`.

## Đã đạt

- Homepage, `/products/`, `/cart/`, `/checkout/` HTTP 200; Blocksy child active và đủ 12 sản phẩm publish.
- Giao diện desktop 1440px/mobile 390px đã kiểm tra bằng browser; trang checkout hiển thị một sản phẩm, tổng `25.000 đ`, free shipping và duy nhất COD.
- Plugin QR/PWA dự án active. QR sản phẩm dùng canonical URL và render local; ba payload đã đối chiếu đúng permalink.
- Manifest/worker/icon HTTP 200; manifest có icon same-origin 192/512 và worker bỏ qua các luồng nhạy cảm/mutable của WooCommerce.
- COD backend acceptance order ID `38`: SKU/quantity/subtotal/total/VND/gateway/status đều đúng; chuyển Processing → Completed thành công; email test bị vô hiệu hóa; order đã xóa đúng ID và hệ thống trở lại `0` order.
- `Smoke-Test.ps1 -Scope All` PASS, gồm HTTP, theme, catalog, plugin, QR, PWA và recent PHP fatal check.

## Đang chờ điều kiện bên ngoài

- PWA chưa installable vì site còn HTTP/IP; đây là kết quả SKIP đúng thiết kế, không phải PASS giả.
- QR/tem hiện chỉ là bản thử vì canonical URL sẽ đổi khi có domain HTTPS.
- Task 14 cần domain thuộc quyền người dùng, quyền DNS/aaPanel hoặc Cloudflare và maintenance window. Chưa thay home/site URL, chưa bật redirect HTTPS.
- Ảnh/logo/liên hệ và catalog hiện vẫn là dữ liệu mẫu, phải được chủ cửa hàng thay bằng dữ liệu chính thức trước mở bán.

## Bằng chứng và rollback

- Baseline verified ngoài VPS: local ignored `backups/20260909T041847Z-baseline`; remote `/www/backup/site/thuc-pham-thuy-trang/20260909T041847Z`.
- Deploy tạo archive rollback từng child theme/plugin tại `/www/backup/site/thuc-pham-thuy-trang/deploy/`.
- Ảnh browser được giữ local trong `backups/visual-check/` và không commit vì là artifact nghiệm thu.
