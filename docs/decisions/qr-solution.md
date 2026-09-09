# Quyết định giải pháp QR sản phẩm

Ngày đánh giá: 2026-09-09.

## Các phương án đã kiểm tra

- **QRCraft 1.0.0** trên WordPress.org: GPL-compatible, PHP 8.0+, nhưng mới dưới 10 lượt cài, cập nhật khoảng 7 tháng trước và chỉ tested tới WordPress 6.9.7. Không chọn cho WordPress 7.1.
- **WPC Product QR Code 1.1.3** trên WordPress.org: cập nhật gần đây, tested tới WordPress 7.1 và tạo Canvas phía browser. Tuy nhiên plugin tạo short URL riêng, có AJAX và một phần statistics premium; không cần thiết cho yêu cầu chỉ dùng canonical product URL.
- **Plugin dự án `tt-product-qr`**: payload luôn là `get_permalink($product_id)`, chỉ nhận HTTP/HTTPS, không tạo shortlink, không gọi dịch vụ ngoài, không telemetry và có endpoint in tem được bảo vệ.

## Quyết định

Dùng plugin dự án `tt-product-qr`. QR được render local trong trình duyệt bằng QRCode.js MIT tại commit `04f46c6a0708418cb7b96fc563eacae0fbf77674`. File vendored có SHA-256:

- `qrcode.min.js`: `c541ef06327885a8415bca8df6071e14189b4855336def4f36db54bde8484f36`
- `LICENSE`: `aaa1f349028a59432eb00222b8ddfe4d2ccebdce04cf8348dcb8d41b7068107b`

Trang sản phẩm hiển thị QR với accessible label. Trang in tem yêu cầu đăng nhập, capability `edit_products`, nonce gắn với product ID và chỉ chứa tên/SKU/giá/canonical URL — không chứa dữ liệu khách hàng hay secret.

Do site hiện dùng HTTP/IP, mọi tem chỉ là bản thử. Chỉ in tem chính thức sau cutover domain/HTTPS ở Task 14 vì canonical URL sẽ thay đổi.
