# Hiện trạng server

Khảo sát chỉ đọc ngày 2026-09-09 qua SSH.

## Kết nối

- Host: `103.77.240.28`
- SSH user: `root`
- SSH port: `2222`
- Host key được ghim khi kết nối: `SHA256:Vkq3xVltsQYnkOzQ5Sow2yEkkURxSPPN1rLdrXsot80`
- Private key nằm ngoài repository; đường dẫn máy hiện tại lưu trong `config/local.ps1` (Git ignored).

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
- Theme đang chạy: `twentytwentyfive`.
- Theme có sẵn: Twenty Twenty-Two, Twenty Twenty-Three, Twenty Twenty-Four, Twenty Twenty-Five.
- Plugin có trên đĩa: Akismet, WooCommerce.
- Plugin đang bật: WooCommerce.
- WP-CLI: chưa cài.
- Sản phẩm publish: 0.
- Đơn hàng publish theo post type cũ: 0.
- Trang publish: 5.
- Upload files: 12.
- Tổng dung lượng web root quan sát được: khoảng 205 MB.
- Không phát hiện `.git` trong website.

## Backup

- Có các thư mục `/www/backup/site` và `/www/backup/database`.
- Tại thời điểm khảo sát không phát hiện file backup website hoặc database trong hai thư mục trên.
- Có backup aaPanel và một số gói phần mềm, nhưng chúng không thay thế backup website/database.

## Cảnh báo đã quan sát

PHP CLI báo một số module đã được nạp lặp (`OPcache`, `zip`, `mbstring`). Đây chưa phải blocker cho website nhưng cần ghi nhận và chỉ chỉnh khi có bằng chứng ảnh hưởng thực tế.

## Cần xác minh trước triển khai

- WordPress `7.1` có phải bản stable/chính thức do aaPanel cài hay không; kiểm tra checksum sau khi có WP-CLI.
- Database thực tế có đúng tên `wp_shop` và backup/restore được hay không, không ghi thông tin đăng nhập ra log.
- Cấu hình backup scheduler của aaPanel.
- Nginx vhost, cron, PHP-FPM và trạng thái HTTPS khi domain được cấp.

<!-- AUTO-INVENTORY:START -->
## Khảo sát tự động gần nhất

``json
{
  "collectedAtUtc": "2026-09-09T04:13:06Z",
  "webRoot": "/www/wwwroot/103.77.240.28",
  "disk": "7168368/51485444/41679504",
  "rootStat": "www:www:755",
  "sourceBytes": 183128389,
  "wordpressVersion": "7.1",
  "siteUrl": "http://103.77.240.28",
  "homeUrl": "http://103.77.240.28",
  "activeTheme": "twentytwentyfive",
  "themes": [
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
      "name": "hello",
      "active": false
    },
    {
      "name": "woocommerce",
      "active": true
    }
  ],
  "counts": {
    "products": 0,
    "orders": 0,
    "pages": 5
  },
  "uploadFiles": 12
}
```
<!-- AUTO-INVENTORY:END -->
