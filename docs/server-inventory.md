# Hiện trạng server

Khảo sát chỉ đọc ngày 2026-09-09 qua SSH.

## Kết nối

- Host: `103.77.240.28`
- SSH user: `root`
- SSH port: `2222`
- Host key được ghim khi kết nối: `SHA256:Vkq3xVltsQYnkOzQ5Sow2yEkkURxSPPN1rLdrXsot80`
- Private key nằm ngoài repository; đường dẫn máy hiện tại lưu trong `config/local.ps1` (Git ignored).
- Từ 2026-09-14, SSH hiệu lực là public-key only: `PasswordAuthentication no`, `KbdInteractiveAuthentication no`, root chỉ được phép dùng public key; port vẫn giữ nguyên `2222`.

## Hệ điều hành và dung lượng

- Hostname: `hoadz98`
- Kernel: Linux `6.8.0-31-generic`, x86_64.
- Phân vùng `/`: 50 GB, đã dùng khoảng 6,9 GB, còn khoảng 40 GB (15% sử dụng lúc khảo sát).

## WordPress

- Web root: `/www/wwwroot/103.77.240.28`
- Owner/group: `www:www`.
- Quyền thư mục gốc: `755`; `wp-content`, themes, plugins, uploads: `775`.
- WordPress core được báo cáo bởi file `wp-includes/version.php`: `7.1`.
- URL site/home: `http://103.77.240.28`.
- Theme đang chạy: `blocksy-child` (parent Blocksy 2.1.56).
- Theme có sẵn: Twenty Twenty-Two, Twenty Twenty-Three, Twenty Twenty-Four, Twenty Twenty-Five.
- Plugin có trên đĩa: Akismet, WooCommerce; plugin mặc định Hello Dolly đã được xóa sau khi xác nhận chính xác.
- Plugin đang bật: WooCommerce.
- WP-CLI 2.12.0 đã được cài tại `/usr/local/bin/wp` từ Phar chính thức, xác minh chữ ký GPG của WP-CLI Releases trước khi cài.
- Sản phẩm publish: 12 sản phẩm mẫu.
- Đơn hàng publish theo post type cũ: 0.
- Trang publish: 7.
- Upload files: 22.
- Tổng dung lượng web root quan sát được: khoảng 205 MB.
- Không phát hiện `.git` trong website.

## Trạng thái storefront sau triển khai 2026-09-09

- Parent theme Blocksy 2.1.56 và child theme `blocksy-child` đang hoạt động; code tùy biến chỉ nằm dưới `wp-content/themes/blocksy-child`.
- WooCommerce 11.1.0 public ở `/products/`, dùng VND 0 số lẻ và COD; coming-soon đã tắt để người dùng kiểm tra.
- Catalog mẫu có 7 danh mục, 12 sản phẩm và một attachment placeholder tự tạo; trang chủ là page ID 13, shop là page ID 7.
- Nginx rewrite WordPress đã được thêm qua file include riêng; backup file rewrite rỗng ban đầu có ở server/local như ghi trong handoff. `nginx -t` và smoke HTTP đều PASS.

## Backup

- Có các thư mục `/www/backup/site` và `/www/backup/database`.
- Tại thời điểm khảo sát không phát hiện file backup website hoặc database trong hai thư mục trên.
- Có backup aaPanel và một số gói phần mềm, nhưng chúng không thay thế backup website/database.

## Cảnh báo đã quan sát

PHP CLI báo một số module đã được nạp lặp (`OPcache`, `zip`, `mbstring`). Đây chưa phải blocker cho website nhưng cần ghi nhận và chỉ chỉnh khi có bằng chứng ảnh hưởng thực tế.

## Trạng thái sau ứng cứu 2026-09-14

- Sự cố WordPress bị xâm nhập đã được làm sạch theo `docs/incidents/2026-09-14-wordpress-compromise.md`.
- WordPress 7.1 và WooCommerce 11.1.0 đã được cài lại từ WordPress.org; checksum cả hai PASS.
- Plugin active: WooCommerce, `tt-product-qr`, `tt-pwa`; plugin File Manager lạ đã bị cô lập ngoài web root.
- Chỉ còn một administrator hợp lệ là `qtri_thuytrang`; mật khẩu và salts đã được xoay.
- `wp-admin`, `wp-includes`, theme/plugin không writable bởi PHP-FPM user `www`; uploads vẫn writable nhưng Nginx từ chối thực thi PHP/PHTML/PHAR trong uploads. PHP core ở web-root vẫn writable và được ghi nhận là việc hardening cần làm tiếp.
- Backup forensic đầy đủ UTC `20260914T041509Z` đã xác minh PASS và có bản ngoài VPS.

## Cần xác minh trước triển khai

- WordPress `7.1` đã được WP-CLI nhận diện và `wp core verify-checksums` PASS ngày 2026-09-09.
- Database thực tế có đúng tên `wp_shop` và backup/restore được hay không, không ghi thông tin đăng nhập ra log.
- Cấu hình backup scheduler của aaPanel.
- Nginx vhost, cron, PHP-FPM và trạng thái HTTPS khi domain được cấp.

<!-- AUTO-INVENTORY:START -->
## Khảo sát tự động gần nhất

```json
{
  "collectedAtUtc": "2026-09-09T07:47:07Z",
  "webRoot": "/www/wwwroot/103.77.240.28",
  "disk": "7273276/51485444/41574596",
  "rootStat": "www:www:755",
  "sourceBytes": 202931708,
  "wordpressVersion": "7.1",
  "siteUrl": "http://103.77.240.28",
  "homeUrl": "http://103.77.240.28",
  "activeTheme": "blocksy-child",
  "themes": [
    "blocksy-child",
    "blocksy",
    "twentytwentyfive",
    "twentytwentyfour",
    "twentytwentythree",
    "twentytwentytwo"
  ],
  "plugins": [
    {
      "name": "akismet",
      "active": false
    },
    {
      "name": "woocommerce",
      "active": true
    }
  ],
  "counts": {
    "products": 12,
    "orders": 0,
    "pages": 7
  },
  "uploadFiles": 22
}
```
<!-- AUTO-INVENTORY:END -->
