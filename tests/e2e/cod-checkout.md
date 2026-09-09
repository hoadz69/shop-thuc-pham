# Kịch bản nghiệm thu checkout COD

Ngày chạy gần nhất: 2026-09-09.

## Fixture cố định

- SKU: `TT-RAU-001` — Cải xanh tươi.
- Số lượng: 1.
- Đơn giá/tạm tính/tổng: `25.000 đ`, tiền tệ `VND`, không có phần thập phân.
- Thanh toán: `cod` — Thanh toán khi nhận hàng.
- Dữ liệu giả: Khách Kiểm thử, `0900000000`, `123 Đường Kiểm Thử`, Hồ Chí Minh, Việt Nam. Không dùng email thật.

## Checklist browser

1. Mở `/checkout/?add-to-cart=23` trong context browser mới.
2. Xác nhận sản phẩm, số lượng, tạm tính và tổng đều đúng; chỉ có COD được chọn.
3. Xác nhận các trường email, tên, họ, địa chỉ và thành phố hiện diện; thử submit thiếu trường bắt buộc phải báo lỗi trước khi chạy đơn thật.
4. Kiểm tra desktop 1440px và mobile 390px không overflow ngang, nút Đặt hàng truy cập được.
5. Khi tạo order thật, ghi ID ngay; kiểm tra trong WooCommerce Admin, chuyển Processing → Completed, sau đó xóa/anonymize đúng ID test.

Ảnh local bị Git ignore: `backups/visual-check/checkout-cod-desktop.png` và `checkout-cod-mobile.png`.

## Kết quả chạy tự động gần nhất

Order test ID `38` được tạo qua WooCommerce CRUD API với email gửi đi đã tắt trong tiến trình test. Đã xác minh SKU `TT-RAU-001`, quantity `1`, subtotal/total `25000`, currency `VND`, payment `cod`, trạng thái `processing` và danh sách gateway enabled chỉ có `cod`. Đơn được chuyển sang `completed`, xóa vĩnh viễn theo đúng ID; kiểm tra cuối `orders=0`, `order38=absent`.

Không dùng fixture này cho đơn thật và không giữ thông tin kiểm thử lâu hơn phiên nghiệm thu.
