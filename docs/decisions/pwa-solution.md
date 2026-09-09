# Quyết định giải pháp PWA

Ngày đánh giá: 2026-09-09.

## Các phương án đã kiểm tra

- **Super Progressive Web Apps 2.2.48**: WordPress.org API ghi 40.000+ active installs, WordPress 5.0+, PHP 5.3+, tested tới 7.1 và cập nhật 2026-09-03. Plugin có manifest, service worker, offline page và clean uninstall, nhưng mô tả chính thức dùng aggressive caching cho mọi trang đã xem.
- **PWA for WP 1.7.88**: 20.000+ active installs, tested tới 7.1 và cập nhật 2026-08-21. Có nhiều tích hợp/extension và dashboard riêng; lớn hơn nhu cầu storefront này.
- **Plugin dự án `tt-pwa`**: GPL-2.0-or-later, không lưu option/database, manifest và worker cùng origin, gỡ plugin không để lại dữ liệu. Cache chỉ navigation/asset public; bỏ qua admin, login, cart, checkout, account, non-GET, nonce, Woo AJAX và add-to-cart.

## Quyết định và checkpoint HTTPS

Dùng `tt-pwa` để chính sách cache WooCommerce có thể audit trực tiếp trong Git. Manifest có đủ name/short name/start URL/display/màu và icon vector khai báo 192/512. Service worker có scope `/` qua header `Service-Worker-Allowed`.

Site hiện tại là `http://103.77.240.28`, không phải secure context. Script đăng ký vì vậy chỉ đặt trạng thái `waiting-https`, không đăng ký service worker và không tuyên bố ứng dụng có thể cài. Sau Task 14 phải kiểm tra lại trên domain HTTPS bằng DevTools/Lighthouse và điện thoại thật.
