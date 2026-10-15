# Đưa Bút Chì Xanh lên InfinityFree (HTTPS công khai)

Bản trên máy dùng nginx, Docker và `.env`. Hosting chia sẻ không có ba thứ đó, nên các lớp bảo vệ đã được chuyển sang:

| Lớp bảo vệ | Trên máy (Docker) | Trên hosting |
|---|---|---|
| Bắt buộc HTTPS, chặn file nhạy cảm | `nginx.conf` | `.htaccess` (từ `htaccess-goc.txt`) |
| 6 tiêu đề bảo mật, ẩn phiên bản PHP | nginx | `nhom25-security.php` + `.htaccess` |
| Chặn XML-RPC, liệt kê tài khoản (REST, `?author=`, sitemap) | nginx + PHP | `nhom25-security.php` |
| Không chạy PHP trong thư mục ảnh tải lên | — | `wp-content/uploads/.htaccess` |
| Khoá VNPay, tài khoản VietQR | `.env` | hằng số trong `wp-config.php` |
| Khoá đăng nhập 5 lần sai, 2FA | plugin | plugin (giữ nguyên) |

**Giới hạn của InfinityFree:** hosting này chặn mọi yêu cầu không đến từ trình duyệt, nên thông báo IPN của VNPay không tới được. Vì vậy trên hosting **để trống khoá VNPay**: cổng VNPay tự ẩn, khách dùng COD hoặc VietQR. Muốn demo VNPay thì dùng Cloudflare Tunnel từ máy.

## Bước 1 — Trên InfinityFree
1. Tạo tài khoản hosting với tên miền con, ví dụ `butchixanh.infinityfree.me`.
2. **SSL Certificates** → cấp chứng chỉ miễn phí cho tên miền đó → chờ báo "Active".
3. **Control Panel → MySQL Databases** → tạo CSDL. Ghi lại: tên CSDL, tên đăng nhập, mật khẩu, máy chủ (`sqlXXX.infinityfree.com`).
4. Cài WordPress bằng trình cài sẵn (Softaculous) vào thư mục gốc, **dùng CSDL vừa tạo**, mật khẩu admin tuỳ ý (sẽ bị thay ở bước 3).

## Bước 2 — Trên máy: tạo gói
```bash
cd 01-cua-hang
bash trien-khai/tao-goi-trien-khai.sh butchixanh.infinityfree.me
```
Script:
- quét bí mật trước (dừng lại nếu thấy khoá trong `wp-content`);
- xuất CSDL đã đổi `butchixanh.local` sang tên miền mới (CSDL trên máy giữ nguyên) và xoá các phiên đăng nhập đang lưu;
- đóng gói `wp-content`, bỏ nhật ký và thư mục tạm.

Kết quả nằm trong `trien-khai/goi/`. Thư mục này đã được gitignore: **không commit, không gửi qua chat**.

## Bước 3 — Tải lên
1. Dùng **FileZilla** (thông tin FTP ở trang tài khoản InfinityFree). Trình quản lý file trên web giới hạn 10 MB nên không dùng được cho gói 31 MB.
2. Giải nén `wp-content.zip` trên máy, rồi tải thư mục `wp-content` lên `htdocs/`, ghi đè.
3. **phpMyAdmin của InfinityFree** → chọn CSDL → *Import* `csdl.sql`. File này thay toàn bộ dữ liệu cài mặc định, kể cả tài khoản admin: đăng nhập bằng **mật khẩu và 2FA giống trên máy**.
4. Mở `htdocs/wp-config.php` (FileZilla → View/Edit):
   - sửa `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` theo bước 1.3 nếu trình cài chưa điền;
   - đảm bảo `$table_prefix = 'wp_';`;
   - thay 8 dòng `AUTH_KEY … NONCE_SALT` bằng bộ mới từ https://api.wordpress.org/secret-key/1.1/salt/;
   - dán nội dung `wp-config-them.php`, phần nằm sau dòng `<?php`, vào ngay trên dòng `/* That's all, stop editing! */`.
5. Tải `htaccess-goc.txt` lên `htdocs/` và đổi tên thành `.htaccess`, ghi đè file cũ. Tải `htaccess-uploads.txt` lên `htdocs/wp-content/uploads/` và đổi tên thành `.htaccess`.
6. Xoá `htdocs/readme.html`, `htdocs/license.txt`, `htdocs/wp-config-sample.php`.

## Bước 4 — Ngay sau khi lên mạng
1. Đăng nhập `https://<tên-miền>/wp-admin` → **đổi mật khẩu admin** (Hồ sơ) → **tạo bộ mã dự phòng 2FA mới**. Bản online không dùng chung bí mật với máy.
2. *Cài đặt → Đường dẫn tĩnh* → bấm **Lưu** một lần để tạo lại đường dẫn.
3. Xoá sản phẩm thử nếu có, kiểm tra trang chủ, cửa hàng, giỏ hàng, thanh toán (COD).
4. Kiểm tra bảo mật từ máy bất kỳ:
   ```bash
   D=https://butchixanh.infinityfree.me
   curl -sI $D | grep -iE "strict-transport|x-frame|content-security|x-content-type|referrer|permissions"   # đủ 6 dòng
   curl -s -o /dev/null -w "%{http_code}\n" $D/xmlrpc.php            # 403
   curl -s -o /dev/null -w "%{http_code}\n" $D/wp-config.php         # 403
   curl -s "$D/?rest_route=/wp/v2/users" | head -c 80               # rest_no_route
   curl -s -o /dev/null -w "%{http_code}\n" "$D/?author=1"           # 404
   curl -s -o /dev/null -w "%{redirect_url}\n" http://${D#https://}  # chuyển sang https
   ```
5. Đăng nhập sai 5 lần (từ cửa sổ ẩn danh) để chắc chắn bị khoá 15 phút.

## Không bao giờ tải lên hosting
`.env` · `TruyCap.txt` · `backup/` (mọi bản sao lưu, file mã 2FA) · `certs/` · `db-init.sql` · `trien-khai/goi/` sau khi dùng xong (xoá trên máy).
