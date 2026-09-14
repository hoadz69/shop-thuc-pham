# Forensic audit VPS sau sự cố WordPress ngày 2026-09-14

## Kết luận

Đã xác nhận kẻ tấn công từng thực thi mã hệ điều hành dưới user PHP-FPM `www` thông qua Alfa webshell. Chưa tìm thấy bằng chứng chúng chiếm quyền `root`, cài persistence cấp hệ điều hành hoặc chạy phần mềm đào coin. Tại thời điểm kiểm tra không có miner/process/container/kết nối pool bất thường và máy gần như idle.

Đây là kết luận từ live forensic, không phải chứng minh tuyệt đối máy chưa từng bị root compromise. Mức bảo đảm cao nhất sau một vụ remote-code-execution vẫn là dựng lại VPS từ image sạch rồi phục hồi dữ liệu đã kiểm tra.

## Bằng chứng có xâm nhập ở quyền `www`

- `/tmp/sess_0143c1e8e97da861c623ff508a441c54.php` là Alfa Team PHP shell, 473.801 byte, SHA-256 `8036c2f8d75d4fafcff9587ab6b015bd84bc0c836e3208924e000e97e6d19550`.
- `/tmp/alfacgiapi/getheader.alfa` là shell script thăm dò compiler/downloader/quyền filesystem, SHA-256 `0b770d781067f06f847e7971a31bedee8c20c1f180e116cedeaa1c48697d7085`.
- Access log cho thấy plugin độc hại `ioxi` từng được gọi, sau đó webshell tự tạo các payload dưới `wp-includes`, `wp-admin` và child theme.
- Các file trên thuộc `www:www`; không có process nào đang giữ/mở hai IOC này tại thời điểm audit.

## Bằng chứng chưa thấy root compromise/mining

- CPU idle 98–100%, load average `0.06/0.02/0.03`; không có process tên/hành vi miner đã biết.
- Không có ELF executable trong `/tmp`, `/var/tmp`, `/dev/shm`; không có Docker container.
- Không có kết nối ra ngoài từ process đáng ngờ hoặc kết nối tới mining pool/stratum quan sát được.
- Không có cron/systemd/init/profile mới từ sau thời điểm xâm nhập; không có `LD_PRELOAD`, SUID/SGID mới hoặc kernel module/rootkit IOC; kernel taint bằng `0`.
- Chỉ `root` có login shell. Lịch sử SSH từ lúc boot chỉ có public-key login từ IP quản trị `210.245.52.70`; không có password login thành công.
- `/root/.ssh/authorized_keys` chỉ có một ED25519 key, fingerprint `SHA256:WCsWoNUPjcthZvo8pHRm1IR/hRleVsIJCSnlvHiELzI`, file có từ trước sự cố.
- WordPress là installation duy nhất dưới `/www/wwwroot` và core checksum hiện PASS.
- aaPanel process trên port `18134`, Nginx trên `888` và `site_total.service` đều có từ trước sự cố/thuộc cấu hình panel; không thấy network activity đáng ngờ từ `site_total`.

## Rủi ro còn lại

- Hai IOC mã độc vẫn còn bất hoạt trong `/tmp` để phục vụ điều tra; chưa quarantine/xóa vì phiên này chỉ được yêu cầu kiểm tra.
- Kẻ tấn công có quyền đọc `wp-config.php` khi webshell hoạt động, do đó phải coi mật khẩu database WordPress đã có khả năng bị lộ.
- `wp-admin`, `wp-includes`, themes và plugins đã read-only với `www`, nhưng các PHP core ở web-root như `wp-login.php` vẫn writable bởi `www`; hardening trước đó chưa bao phủ hết.
- SSH chịu brute-force lớn: hơn 40.000 lần fail từ lúc boot. Fail2ban đang hoạt động và đã ban IP, nhưng `PasswordAuthentication yes` vẫn bật dù root chỉ cho public key.
- UFW đang mở công khai các port `20`, `21`, `22`, `888`, `18134`, `39000:40000` ngoài `80/443/2222`; hiện FTP không listen nhưng rule dư làm tăng attack surface. MariaDB bind `0.0.0.0:3306` nhưng UFW không allow và không có remote connection tại thời điểm kiểm tra.
- Một số process hệ thống trỏ executable đã được nâng cấp/xóa trên đĩa; đây phù hợp với package update trước đó, nhưng nên reboot trong maintenance window.

## Hành động khuyến nghị

1. Backup/hash rồi quarantine toàn bộ IOC còn lại trong `/tmp`.
2. Rotate database password và cập nhật `wp-config.php` theo quy trình có rollback; WordPress admin password/salts đã được rotate ở bước ứng cứu trước.
3. Chuyển toàn bộ PHP core ở web-root sang `root:www`, file `644`, directory `755`; giữ chỉ uploads writable và tiếp tục chặn PHP trong uploads.
4. Tắt SSH password authentication, giữ nguyên port `2222` và public-key login; kiểm tra `sshd -t` trước reload.
5. Giới hạn aaPanel port `18134` theo IP quản trị/VPN; rà và đóng các UFW rule `20/21/22/888/39000:40000` nếu không thực sự dùng.
6. Reboot trong maintenance window, rồi lặp lại process/network/persistence scan và theo dõi log ít nhất 24–48 giờ.
7. Nếu cần mức bảo đảm cao nhất, dựng lại VPS từ image Ubuntu sạch và phục hồi site từ code/database/uploads đã kiểm tra.

## Cập nhật khắc phục SSH 2026-09-14

- Đã tạo và xác minh backup website `20260914T082231Z`; cấu hình SSH trước thay đổi được lưu tại `/www/backup/site/thuc-pham-thuy-trang/ssh/20260914T082231Z`.
- Đã cài `/etc/ssh/sshd_config.d/00-thuytrang-key-only.conf` để giá trị an toàn được đọc trước `50-cloud-init.conf`; giữ nguyên port `2222`, `PubkeyAuthentication yes`, `PermitRootLogin prohibit-password`, tắt `PasswordAuthentication` và keyboard-interactive authentication.
- `sshd -t` PASS, service vẫn active sau reload, kết nối mới bằng key PASS và phép thử ép password-only bị từ chối. Không thay đổi UFW/firewall hoặc aaPanel.
