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

## Xử lý đơn COD

1. Mở **WooCommerce → Đơn hàng** và đối chiếu tên, số điện thoại, địa chỉ, sản phẩm, số lượng và tổng tiền.
2. Với đơn mới hợp lệ, chuyển trạng thái theo quy trình thực tế của cửa hàng, ví dụ **Đang xử lý** rồi **Đã hoàn thành** sau khi giao.
3. Ghi chú nội bộ không được chứa mật khẩu, số thẻ hoặc dữ liệu không cần thiết.
4. Chỉ hủy/xóa đơn khi có quy trình nghiệp vụ rõ ràng; không xóa hàng loạt.

## Dữ liệu mẫu

Catalog hiện có 12 sản phẩm mẫu với SKU bắt đầu bằng `TT-`. Trước khi mở bán thật, thay tên/giá/ảnh/mô tả bằng dữ liệu được chủ cửa hàng duyệt. Script seed dùng SKU làm khóa và có thể ghi đè lại dữ liệu mẫu nếu chạy với `-Apply`.

## Trước thay đổi lớn

Chạy backup và verifier theo `docs/runbooks/continuity-and-recovery.md`, sau đó mới cập nhật plugin/theme hoặc nhập dữ liệu quy mô lớn.
