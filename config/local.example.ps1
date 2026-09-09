# Copy file này thành config/local.ps1 và điền giá trị trên máy cá nhân.
# config/local.ps1 bị Git bỏ qua. ProjectSshKeyPath phải trỏ tới private key
# định dạng PuTTY PPK; scripts dùng plink/pscp và luôn ghim ProjectHostKey.
# ProjectHostKey dùng dạng SHA256: theo PuTTY/OpenSSH với digest Base64 43 ký tự.
$ProjectServerHost = "103.77.240.28"
$ProjectSshPort = 2222
$ProjectSshUser = "root"
$ProjectSshKeyPath = "C:\\path\\to\\private-key.ppk"
$ProjectHostKey = "SHA256:replace-with-verified-host-fingerprint"
$ProjectWebRoot = "/www/wwwroot/103.77.240.28"
