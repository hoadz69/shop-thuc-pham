# Quản lý sản phẩm và đơn hàng

## Đăng nhập

Truy cập `/wp-admin` bằng tài khoản quản trị do chủ cửa hàng quản lý. Không gửi mật khẩu qua chat hoặc ghi vào repository.

## Thêm hoặc sửa sản phẩm

1. Mở **Sản phẩm → Tất cả sản phẩm**; chọn sản phẩm cần sửa hoặc **Thêm mới**.
2. Nhập tên, mô tả, mô tả ngắn và ảnh thật có quyền sử dụng.
3. Trong **Dữ liệu sản phẩm**, nhập giá bán bằng VNĐ, tồn kho/SKU và đơn vị phù hợp.
4. Chọn đúng một hoặc nhiều danh mục; kiểm tra đường dẫn trước khi bấm **Đăng/Cập nhật**.
5. Mở trang sản phẩm ở cửa sổ riêng, kiểm tra desktop/mobile và thử thêm vào giỏ.

Không sửa trực tiếp dữ liệu bằng phpMyAdmin nếu thao tác tương ứng có trong WordPress.

## Sửa nhanh tên website và thông tin liên hệ

1. Mở **Giao diện → Tùy biến → Thông tin cửa hàng**.
2. Tại đây có thể sửa tập trung tên website, mô tả ngắn, số điện thoại, email, địa chỉ, giờ làm việc, Facebook và Zalo.
3. Chọn **Đăng** rồi tải lại trang chủ để kiểm tra footer trên máy tính và điện thoại.

Tên website dùng cài đặt chuẩn `blogname` của WordPress nên thay đổi sẽ áp dụng cho cả header, tiêu đề trang và footer. Giờ làm việc hoặc liên kết mạng xã hội để trống sẽ được ẩn, tránh xuất hiện nhãn không có nội dung. Danh mục ở footer được lấy tự động từ **Sản phẩm → Danh mục**, không cần sửa trong code.

Form bản tin gửi thông báo đăng ký đến **Email liên hệ** trong mục này và không lưu danh sách email vào database WordPress. Việc gửi thư còn phụ thuộc cấu hình mail của máy chủ; nếu gửi thất bại, giao diện sẽ báo người dùng thử lại.

## Xử lý đơn COD

1. Mở **WooCommerce → Đơn hàng** và đối chiếu tên, số điện thoại, địa chỉ, sản phẩm, số lượng và tổng tiền.
2. Với đơn mới hợp lệ, chuyển trạng thái theo quy trình thực tế của cửa hàng, ví dụ **Đang xử lý** rồi **Đã hoàn thành** sau khi giao.
3. Ghi chú nội bộ không được chứa mật khẩu, số thẻ hoặc dữ liệu không cần thiết.
4. Chỉ hủy/xóa đơn khi có quy trình nghiệp vụ rõ ràng; không xóa hàng loạt.

## Dữ liệu mẫu

Catalog hiện có 12 sản phẩm mẫu với SKU bắt đầu bằng `TT-`. Trước khi mở bán thật, thay tên/giá/ảnh/mô tả bằng dữ liệu được chủ cửa hàng duyệt. Script seed dùng SKU làm khóa và có thể ghi đè lại dữ liệu mẫu nếu chạy với `-Apply`.

## Trước thay đổi lớn

Chạy backup và verifier theo `docs/runbooks/continuity-and-recovery.md`, sau đó mới cập nhật plugin/theme hoặc nhập dữ liệu quy mô lớn.
