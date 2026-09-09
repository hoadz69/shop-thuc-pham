# Trạng thái phiên hiện tại

Cập nhật: 2026-09-09, múi giờ Asia/Ho_Chi_Minh.

## Sau khi khởi động lại máy

- Mở Codex với workspace tuyệt đối: `E:\Projects\thuc-pham-thuy-trang`.
- Gửi yêu cầu: `Đọc AGENTS.md và docs/sessions/CURRENT.md, kiểm tra Git rồi tiếp tục đúng bước tiếp theo.`
- Repository ở ổ E là bản làm việc chính. Bản cũ ở ổ C chỉ là dự phòng và không được chỉnh song song.
- File `config/local.ps1` ở bản ổ E chứa metadata kết nối riêng của máy và bị Git ignore.
- Private key vẫn nằm ngoài repository tại đường dẫn được tham chiếu trong `config/local.ps1`.

## Mục tiêu đang thực hiện

Đang thực thi liên tục implementation plan 14 task để hoàn thiện và deploy bản website chạy qua IP hiện tại. Task 1 đã hoàn thành; mọi thay đổi VPS vẫn bị chặn cho đến khi baseline backup ngoài VPS được tạo và xác minh.

## Phiên đang làm

- Phiên 2026-09-09 hiện tại: tiếp tục từ Task 2 đến khi có giao diện deploy trên VPS để người dùng kiểm tra bằng mắt; giữ nguyên `.git`, `config/local.ps1` và không ghi đè thay đổi không rõ nguồn gốc.
- Working tree được xác nhận sạch trên `main` trước khi bắt đầu. Mọi khảo sát VPS vẫn chỉ đọc cho đến khi baseline backup ngoài VPS vượt qua kiểm tra checksum/cấu trúc.
- Người dùng từng yêu cầu dừng khi hạn mức còn khoảng 2%, sau đó yêu cầu tiếp tục ngay trong cùng phiên. Một commit checkpoint đã được tạo trước khi tiếp tục; không bắt đầu Task 10–14 trong checkpoint giao diện này.
- Đã đọc lại `AGENTS.md`, handoff, server inventory, project brief, runbook và thiết kế đã duyệt.
- Git ở nhánh `main`, commit gần nhất trước khi hoàn thiện plan là `d87401e`; các thay đổi tài liệu dang dở đã được rà soát và tiếp tục, không bị ghi đè.
- Đã nạp `config/local.ps1` mà không in secret; mọi khảo sát server tiếp theo vẫn phải chỉ đọc cho đến khi baseline backup được tạo và xác minh.
- Đã hoàn thiện và tự rà soát `docs/superpowers/plans/2026-09-09-thuc-pham-woocommerce-implementation.md`; plan gồm 14 task, có cổng backup bắt buộc trước thay đổi WordPress.
- Phiên hiện tại tiếp tục trực tiếp trên repository chính theo yêu cầu người dùng, dùng TDD và review theo từng task; không tạo/xóa worktree và không ghi đè thay đổi handoff sẵn có.
- Task 1 đã thêm thư viện kết nối dùng PuTTY PPK, ghim host fingerprint và kiểm thử argument array hoàn toàn offline; không có kết nối hay thay đổi VPS trong task này.

## Quyết định đã được người dùng duyệt

- Chọn phương án 3: project local + Git + backup ngoài VPS + deploy có kiểm soát.
- Codex được phép tự kết nối và khảo sát server bằng SSH key do người dùng cung cấp.
- Phải viết đủ tri thức và nhật ký để phiên Codex khác tiếp tục nếu phiên hiện tại bị mất.

## Đã hoàn thành

- Đọc project brief gốc.
- Đối chiếu website mẫu ở mức nội dung/bố cục.
- Xác nhận private key là PuTTY PPK v3, loại `ssh-ed25519`.
- Ghim host fingerprint và SSH thành công ở chế độ chỉ đọc.
- Khảo sát filesystem, WordPress, theme/plugin, nội dung cơ bản và backup hiện có.
- Tạo cấu trúc repository local và bộ tài liệu nền tảng.
- Chuẩn bị sao chép nguyên repository và Git history sang `E:\Projects\thuc-pham-thuy-trang` để dùng sau khi restart.
- Viết implementation plan chi tiết từ brief và kiến trúc đã duyệt; sửa dependency để verifier được tạo trước khi backup thật và deploy/smoke foundation được tạo trước lần sử dụng đầu tiên.
- Hoàn thành Task 1 với `scripts/lib/Project.Common.ps1`, cấu hình mẫu PPK và 16 test Pester cho validation/scope/SSH/SCP. Chu kỳ TDD: RED 0 pass/16 fail do helper chưa tồn tại; GREEN 16 pass/0 fail bằng Pester 6.1.0.
- Xác minh `config/local.ps1` thật nạp thành công mà không in giá trị: đúng sáu field, key có đuôi PPK, `plink` và `pscp` đều khả dụng. Không cần backup vì Task 1 chỉ thay đổi code/tài liệu local và test mock toàn bộ network.
- Commit Task 1: `build: add safe server connection helpers` (chính commit chứa handoff này; xem `git log -1`). Commit hoàn tất gần nhất trước Task 1: `b68fe14`.
- Sau quality review, harden Task 1 để `plink`/`pscp` dừng bằng lỗi chỉ chứa tên tool và exit code khi native process trả nonzero; web root chỉ nhận một segment an toàn `[A-Za-z0-9][A-Za-z0-9._-]*`; fingerprint phải có digest Base64 43 ký tự. Test bổ sung bao phủ `.`, `..`, khoảng trắng, newline, `;`, `$()`, backtick và lỗi native không rò command/key/fingerprint.
- Chu kỳ TDD cho fix review: RED 16 pass/4 fail đúng bốn hành vi thiếu; GREEN 20 pass/0 fail bằng Pester 6.1.0. Fingerprint trong `config/local.ps1` thật khớp grammar mới khi kiểm tra boolean, không in giá trị. Commit fix: `fix: harden server connection helpers` (chính commit chứa handoff này; xem `git log -1`).
- Sau review vòng hai, regex web root và fingerprint dùng `\z` để cấm LF cuối; fingerprint dùng so khớp phân biệt hoa thường và chỉ chấp nhận prefix chính xác `SHA256:`. Chu kỳ TDD: RED 19 pass/3 fail đúng ba boundary case; GREEN 22 pass/0 fail bằng Pester 6.1.0. Commit fix: `fix: enforce strict config boundaries` (chính commit chứa handoff này; xem `git log -1`).
- Task 1 đã qua review yêu cầu và review chất lượng độc lập; không còn issue Critical/Important/Minor. Checkbox Task 1 trong implementation plan đã được đánh dấu để phiên mới tiếp tục đúng Task 2.
- Task 2 đã thêm preflight và inventory chỉ đọc có thể lặp lại. TDD: RED 0/3 do script chưa tồn tại; GREEN 3/3 bằng Pester 6.1.0. Khảo sát thật xác nhận host `hoadz98`, UID 0, web root `www:www:755`, còn 41.679.504 KiB, các binary backup có sẵn và WP-CLI chưa có.
- Inventory tự động lúc `2026-09-09T04:13:06Z`: WordPress 7.1, theme `twentytwentyfive`, WooCommerce bật, Akismet/Hello tắt, 0 sản phẩm, 0 đơn kiểu post, 5 trang và 12 upload. PHP CLI vẫn báo OPcache/zip/mbstring nạp lặp; chưa thay đổi server.
- Tasks 3–4 đã tạo baseline UTC `20260909T041847Z` tại server `/www/backup/site/thuc-pham-thuy-trang/20260909T041847Z` và local ignored `backups/20260909T041847Z-baseline`. Remote checksum 4/4 OK; local verifier PASS checksum, gzip/tar, path safety, uploads có file và các bảng hậu tố `_options`, `_posts`, `_postmeta` cùng marker dữ liệu.
- Kích thước artifact: DB 304.976 byte, uploads 19.268 byte, source 58.853.291 byte, environment 245 byte; file/manifest quyền `600`, thư mục backup remote `700`. Pester backup/verifier PASS 9/9, gồm fixture checksum sai, SQL thiếu cấu trúc WordPress và uploads rỗng. Restore drill tạm đã được ghi vào runbook; production chưa bị trỏ vào database khác.
- Task 5 đã xác minh lại baseline PASS, rồi cài WP-CLI 2.12.0 vào `/usr/local/bin/wp` từ Phar/ASC/key chính thức qua TLS; GPG báo chữ ký tốt với fingerprint `63AF 7AA1 5067 C056 16FD DD88 A3A2 E8F2 26F0 BC06`. Pester installer PASS 3/3.
- `wp core version` nhận diện WordPress 7.1 và `wp core verify-checksums` PASS. Vì core chính thức/hợp lệ, cổng chuẩn hóa WordPress được phép tiếp tục.
- Task 6: cài Blocksy 2.1.56 chính thức, deploy/kích hoạt `blocksy-child` lần đầu và smoke Theme PASS. Child theme chứa homepage, Woo hooks, CSS responsive, JS progressive enhancement và hai ảnh tự tạo bằng built-in image generation: `hero-fresh-market.png`, `product-placeholder.png` (không sao chép site mẫu).
- Task 7: trước xóa đã liệt kê rõ post/page/plugin. Đã xóa đúng post mặc định ID 1, page mẫu ID 2 và plugin `hello` 1.7.2; giữ nguyên các page WooCommerce và Akismet. Desired state chạy hai lần vẫn dùng home ID 13/shop ID 7; timezone, permalink, VND 0 decimals, COD và slug `/products`, `/cart`, `/checkout` đã cấu hình. WooCommerce coming-soon đã tắt bằng hai option chính thức để public có thể xem site.
- Task 8: seed 7 danh mục/12 sản phẩm chạy hai lần, cả hai lần trả đúng 7/12; SKU là khóa upsert, ảnh placeholder được import một lần. Pester seed PASS 4/4.
- Nginx pretty permalink ban đầu 404 vì file `/www/server/panel/vhost/rewrite/103.77.240.28.conf` rỗng. Đã tải bản gốc ra local ignored `backups/20260909T044157Z-nginx-rewrite`, lưu remote dưới `/www/backup/site/thuc-pham-thuy-trang/nginx/20260909T044157Z-103.77.240.28.conf`, áp `config/nginx-wordpress-rewrite.conf`, `nginx -t` PASS và reload thành công. Sau đó smoke Catalog PASS cho `/`, `/products/`, `/cart/`, `/checkout/`, theme state và 12 products.
- Task 9 hoàn thành và đã deploy. Lần deploy bản sửa mobile đầu tiên thất bại an toàn trước copy vì PowerShell `Split-Path` biến remote parent thành backslash; source đã sửa dùng `Substring/LastIndexOf('/')`, sau đó deploy lại thành công và tạo rollback theme như thiết kế.
- Visual review bằng Playwright/Edge đạt ở desktop 1440px và mobile thật 390px: không overflow ngang, không còn title shop trùng, hero/bốn cam kết/danh mục/product grid rõ, hamburger cùng bottom nav 4 mục truy cập được. Ảnh bằng chứng local ignored nằm trong `backups/visual-check` (`products-desktop-approved.png`, `home-mobile-approved.png`).
- Đã liệt kê taxonomy trước thay đổi, rồi chuyển `default_product_cat` từ term ID 15 sang ID 16 và xóa đúng term rỗng ID 15 “Chưa phân loại”; hiện còn đúng 7 danh mục mong muốn. Smoke Catalog cuối PASS cho HTTP, Blocksy child và 12 sản phẩm. Toàn bộ Pester tại checkpoint PASS 51/51.
- Giao diện public để người dùng kiểm tra bằng mắt: `http://103.77.240.28/`; shop: `http://103.77.240.28/products/`. Commit checkpoint source đầu tiên: `3a7b617`; commit chứa handoff hoàn tất Task 9 là commit mới nhất (xem `git log -1`).
- Task 10 hoàn thành: nội dung version-control cho `/gioi-thieu/` và `/lien-he/` được upsert, không có contact giả/script/iframe; cả hai HTTP 200. Hướng dẫn quản trị sản phẩm và đơn COD nằm tại `docs/operations/product-and-order-management.md`; Pester cấu hình PASS 7/7. Đã xác nhận có đúng 1 administrator, tạo/sửa/xóa sạch đúng một draft product workflow test; kiểm tra lại không còn draft test.

## Hiện trạng quan trọng

- Server đang chạy website trực tiếp từ `/www/wwwroot/103.77.240.28`.
- Chưa có WP-CLI, Git repo trên server hoặc backup site/database quan sát được.
- Website hiện gần như trống: 0 sản phẩm, 0 đơn hàng, theme mặc định.
- Chưa thực hiện thay đổi nào trên server trong các phiên khảo sát.

## Bước tiếp theo

1. Tiếp tục Task 11–13: QR, PWA checkpoint HTTP và COD acceptance.
2. Hoàn tất push GitHub sau khi người dùng xác nhận cửa sổ đăng nhập tài khoản `hoadz69`; remote đã chọn là `https://github.com/hoadz69/shop-thuc-pham.git`.
3. Task 14 vẫn chờ domain/quyền DNS/SSL; website IP tiếp tục vận hành và QR/PWA chưa được tuyên bố hoàn chỉnh.

## Việc chưa chốt

- Quyền ghi `.git` đã hoạt động; các commit nhỏ theo task đang được tạo bình thường.
- Logo, thông tin liên hệ, banner và ảnh sản phẩm chính thức.
- Mức độ mô phỏng chi tiết giao diện website mẫu.
- Domain cuối cùng và tài khoản Cloudflare/registrar.
- Plugin QR miễn phí phù hợp WordPress/PHP hiện tại; cần khảo sát trước khi cài.

## Quy tắc kết thúc mỗi phiên

Cập nhật file này với: thay đổi đã làm, kiểm thử và kết quả, backup/rollback liên quan, vấn đề còn lại, bước tiếp theo và hash commit mới nhất.
