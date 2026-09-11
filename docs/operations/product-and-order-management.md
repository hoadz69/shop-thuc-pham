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

## In tem QR sản phẩm

1. Mở **Sản phẩm → In tem QR** trong `wp-admin`.
2. Đánh dấu các mặt hàng cần in; có thể chọn tất cả bằng ô ở đầu bảng.
3. Nhập **Số tem** cho từng mặt hàng, từ 1 đến 50. Mỗi lần in tối đa 200 tem để tránh làm treo trình duyệt hoặc hàng đợi máy in.
4. Chọn **Mở bản in tem đã chọn**, kiểm tra tên, giá, SKU và QR rồi bấm **In tem**.
5. Trong hộp thoại máy in, chọn đúng máy in tem và khổ giấy **50 × 35 mm**, tỷ lệ 100%, tắt header/footer của trình duyệt nếu có.
6. Quét thử ít nhất một tem trong lô trước khi dán; QR phải mở đúng trang chi tiết sản phẩm trên `https://thucphamthuytrang.site`.

Khi đang đăng nhập bằng Administrator hoặc Shop Manager, có thể mở trực tiếp một trang chi tiết sản phẩm ngoài storefront và chọn **In tem QR** bên dưới mã QR để in riêng mặt hàng đó. Nút này không hiển thị cho khách truy cập thông thường.

QR chỉ lưu URL canonical của sản phẩm, không lưu giá. Đổi giá không làm hỏng QR, nhưng tem có in giá nên cần in lại nếu giá thay đổi. Không tự ý đổi **đường dẫn tĩnh/slug** sau khi đã in tem; nếu bắt buộc đổi, phải tạo redirect hoặc in lại tem để tránh QR cũ mở trang lỗi.

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
