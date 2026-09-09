# Project brief chuẩn hóa

## Mục tiêu

Dựng website thương mại điện tử bán thực phẩm bằng WordPress + WooCommerce, tham khảo bố cục và trải nghiệm của `https://thucphamthuytrang.com/`. Không sao chép source của website mẫu.

Website cần có:

- Trang chủ, cửa hàng, danh mục, chi tiết sản phẩm, tìm kiếm.
- Giỏ hàng và checkout thanh toán COD.
- Trang Giới thiệu và Liên hệ.
- `wp-admin` để khách hàng tự quản lý sản phẩm và đơn hàng.
- Mỗi sản phẩm có QR mở đúng URL chi tiết, phục vụ in tem nhiệt.
- PWA để người dùng có thể cài website lên điện thoại.

## Ràng buộc

- Ngân sách tổng khoảng 5.000.000 VNĐ; tối ưu chi phí.
- Chỉ dùng theme/plugin miễn phí hoặc mã tự phát triển.
- Không dùng Flatsome crack/null và không cài phần mềm không rõ nguồn gốc.
- Giữ nguyên SSH port `2222`, aaPanel và cấu trúc nhiều website trên server.
- Mỗi website dùng thư mục và database riêng.
- Mật khẩu/private key không được ghi vào Git hoặc tài liệu chia sẻ.

## Hạ tầng đầu vào

- VPS Ubuntu: `103.77.240.28`, khoảng 7,5 GB RAM, ổ 50 GB.
- aaPanel Free 8.0.6.
- Nginx 1.30.4, MariaDB 10.11, PHP 8.3, phpMyAdmin 5.2.
- Web root: `/www/wwwroot/103.77.240.28`.
- Database theo brief: `wp_shop`; thông tin xác thực cung cấp riêng.
- WordPress tiếng Việt và WooCommerce đã được cài.
- COD đã bật; cổng thanh toán khác tắt.

## Phạm vi triển khai

1. Tạo backup baseline và kiểm tra khả năng đọc backup.
2. Cài WP-CLI nếu chưa có.
3. Cài, kích hoạt Blocksy và tạo Blocksy child theme cho tùy biến.
4. Cấu hình permalink, timezone và định dạng VNĐ.
5. Chỉ xóa bài/trang/plugin mặc định sau khi kiểm tra chính xác từng mục.
6. Tạo bảy danh mục:
   - Rau củ quả tươi
   - Thịt heo
   - Thịt bò
   - Gia cầm
   - Hải sản
   - Đồ khô
   - Thực phẩm chế biến
7. Nhập 10-15 sản phẩm mẫu có tên, giá, ảnh, mô tả, danh mục và đơn vị.
8. Dựng trang chủ màu xanh lá: banner/slider, bốn cam kết, lưới sản phẩm nổi bật và khối “Thực phẩm tươi sạch mỗi tuần”.
9. Đặt slug cửa hàng là `/products`, sidebar trái có danh mục và lọc giá.
10. Tạo trang Giới thiệu và Liên hệ.
11. Chọn, cài và kiểm thử plugin QR miễn phí tương thích phiên bản hiện tại.
12. Cài PWA; kiểm thử đầy đủ sau khi có HTTPS.
13. Kiểm thử chọn sản phẩm → giỏ hàng → checkout COD → đơn hàng trong Admin.
14. Viết báo cáo bàn giao và hướng dẫn thêm/sửa sản phẩm.

## Sau khi có domain

- Trỏ DNS về VPS, gắn domain trong aaPanel.
- Cấp Let’s Encrypt, ép HTTPS và cấu hình Cloudflare Full (strict).
- Đổi URL WordPress an toàn, search-replace dữ liệu tuần tự hóa bằng WP-CLI hoặc công cụ tương thích.
- Xóa cache, kiểm thử link, checkout, PWA và QR; sau đó mới in tem QR chính thức.

## Tiêu chí hoàn thành

- Các trang và luồng trong phạm vi hoạt động trên desktop/mobile.
- Khách hàng thêm sản phẩm và xử lý đơn hàng từ `wp-admin` được.
- QR của sản phẩm trỏ đúng URL sau khi chốt domain.
- Có Git history, backup ngoài VPS, hướng dẫn khôi phục và handoff đủ cho phiên mới tiếp tục.
