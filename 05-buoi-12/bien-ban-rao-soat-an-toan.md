# Biên bản rà soát an toàn — Bút Chì Xanh (Buổi 12 · NV2)

Ngày rà soát: 02/10/2026 · Đối tượng: https://butchixanh.local (WordPress + WooCommerce trên Docker)
Công cụ: `tools/audit.sh` (kiểm tra từ bên ngoài bằng curl) + WP-CLI trong container.
Minh chứng: `minh-chung/00-audit-truoc-khi-sua.txt` (trước), `minh-chung/01-audit-sau-khi-sua.txt` (sau), `minh-chung/09-thu-phuc-hoi-sao-luu.txt`.

Phạm vi sửa trong buổi này theo kế hoạch nhóm: **xóa tiện ích không dùng, MFA, giới hạn đăng nhập, thử phục hồi sao lưu**. Các hạng mục không đạt nằm ngoài phạm vi được ghi rõ biện pháp đề xuất.

## Tổng hợp

| # | Nhóm | Hạng mục | Trước | Sau | Ghi chú |
|---|---|---|---|---|---|
| 1 | Kênh truyền | HTTPS trên toàn bộ site | Đạt | Đạt | |
| 2 | Kênh truyền | Chuyển hướng bắt buộc | **Không đạt** | **Không đạt** | Đề xuất sửa (ngoài phạm vi buổi này) |
| 3 | Danh tính | Xác thực nhiều yếu tố cho quản trị | **Không đạt** | Đạt | Đã sửa |
| 4 | Danh tính | Phân quyền tối thiểu | Đạt (có lưu ý) | Đạt (có lưu ý) | Mật khẩu admin yếu |
| 5 | Danh tính | Giới hạn số lần thử đăng nhập | **Không đạt** | Đạt | Đã sửa |
| 6 | Nền tảng | Cập nhật vá lỗi mới nhất | **Không đạt** | Đạt một phần | WordPress đã vá; WooCommerce chưa |
| 7 | Nền tảng | Rà soát tiện ích mở rộng | **Không đạt** | Đạt một phần | Đã xóa 3 plugin; còn 2 theme thừa |
| 8 | Nền tảng | Ẩn thông báo lỗi chi tiết | Đạt (có lưu ý) | Đạt (có lưu ý) | Không lộ đường dẫn; còn lộ phiên bản |
| 9 | Dữ liệu | Sao lưu đã thử phục hồi | **Không đạt** (chưa có) | Đạt | Đã sửa |
| 10 | Dữ liệu | Rà soát mã theo dõi bên thứ ba | Đạt | Đạt | Tắt thêm Gravatar, Order Attribution |

## Chi tiết từng hạng mục

### 1. HTTPS trên toàn bộ site — Đạt
- **Cách kiểm tra:** mở 7 trang chính bằng `https://`; tìm tài nguyên tải qua `http://` (mixed content) trên trang chủ, trang sản phẩm, trang thanh toán.
- **Kết quả:** mọi trang trả về qua HTTPS (200/302); không có tài nguyên `http://` nào được tải.
- **Lưu ý:** chứng chỉ trong môi trường thực hành là chứng chỉ tự ký nên trình duyệt cảnh báo (`04-minh-chung/08-https-canh-bao-loi.png`). Khi lên máy chủ thật dùng chứng chỉ Let's Encrypt.

### 2. Chuyển hướng bắt buộc — Không đạt (chưa sửa)
- **Cách kiểm tra:** gõ địa chỉ không có `https://`.
- **Kết quả:** `http://butchixanh.local/...` → 301 sang `https://` (**đạt**). Nhưng cổng **8081** đang mở thẳng vào WordPress bằng HTTP, bỏ qua nginx: `http://localhost:8081/wp-login.php` vẫn mở được, nên mật khẩu có thể đi qua kết nối không mã hóa. Không có header HSTS.
- **Biện pháp đề xuất:** bỏ dòng `ports: "8081:80"` của dịch vụ `shop` trong `docker-compose.yml` (nginx đã truy cập `shop` qua mạng nội bộ Docker); thêm `add_header Strict-Transport-Security "max-age=31536000" always;` vào khối `server` 443 của `nginx.conf`.

### 3. Xác thực nhiều yếu tố cho quản trị — Không đạt → **Đạt**
- **Trước:** đăng nhập admin chỉ cần mật khẩu.
- **Đã sửa:** cài plugin **Two Factor 0.16.0** (của nhóm phát triển WordPress.org; bản 0.17 yêu cầu WordPress 7.0 nên chưa dùng được). Tài khoản `admin` bật **TOTP** (mã 6 số từ Google/Microsoft Authenticator) làm cách chính, kèm 10 **mã dự phòng**.
- **Bàn giao:** khóa TOTP và mã dự phòng nằm trong `01-cua-hang/backup/admin-2fa-ma-du-phong.txt` (đã đưa vào `.gitignore`, **không** lên GitHub). Người quản trị quét/nhập khóa vào ứng dụng xác thực, sau đó cất file ở nơi an toàn.
- **Kiểm tra lại:** đăng nhập đúng mật khẩu → WordPress hiện thêm màn hình nhập mã xác thực, chưa cấp cookie đăng nhập (xem mục "Kiểm tra MFA" trong `minh-chung/01-audit-sau-khi-sua.txt`).

### 4. Phân quyền tối thiểu — Đạt (có lưu ý)
- **Cách kiểm tra:** `wp user list --role=administrator`.
- **Kết quả:** chỉ có **1** tài khoản quản trị (`admin`, admin@butchixanh.vn), không có tài khoản thừa; đăng ký tài khoản khách đang tắt.
- **Lưu ý:** mật khẩu `admin123` yếu và được ghi trong `README.md` công khai trên GitHub; tên đăng nhập `admin` lộ qua `/?author=1` và `/wp-json/wp/v2/users`. MFA (mục 3) và khóa đăng nhập (mục 5) giảm rủi ro này, nhưng **đề xuất** đổi mật khẩu mạnh, xóa mật khẩu khỏi README và chặn hai đường liệt kê tên đăng nhập.

### 5. Giới hạn số lần thử đăng nhập — Không đạt → **Đạt**
- **Trước:** sai mật khẩu 8 lần liên tiếp không bị chặn; thông báo lỗi xác nhận "mật khẩu cho tên người dùng admin không chính xác" (lộ việc tài khoản tồn tại).
- **Đã sửa:** `wp-content/mu-plugins/nhom25-security.php` — sai **5 lần** → khóa IP **15 phút** (cả khi sau đó nhập đúng mật khẩu); thông báo lỗi chung "tên đăng nhập hoặc mật khẩu không đúng. Còn N lần thử." Lấy IP thật của khách từ header `X-Real-IP` do nginx gắn.
- **Kiểm tra lại:** lần 1–4 "Còn 4…1 lần thử"; lần 5–8 "Tạm khóa: đăng nhập sai quá 5 lần. Vui lòng thử lại sau 15 phút."
- **Sự cố khi kiểm tra:** bản đầu tiên ở đúng lần sai thứ 5 vẫn lộ thông báo gốc của WordPress. Đã sửa: khi đã khóa thì luôn trả thông báo khóa.

### 6. Cập nhật vá lỗi mới nhất — Không đạt → Đạt một phần
- **Trước:** WordPress 6.9.4 (có bản vá 6.9.9), WooCommerce 9.7.1 (đã có 10.9.4), theme Twenty Twenty-Three/Four/Five có bản mới.
- **Sau:** WordPress **6.9.9** (bản vá bảo mật cùng nhánh). WooCommerce vẫn **9.7.1**.
- **Biện pháp đề xuất:** cập nhật WooCommerce lên **10.9.4** (bản mới nhất chạy được trên WordPress 6.9; bản 11.x yêu cầu WordPress 7.0). Nhóm đã thử nâng cấp: site chạy bình thường. Đã hoàn tác vì không nằm trong kế hoạch buổi này. Luôn sao lưu (mục 9) trước khi cập nhật.

### 7. Rà soát tiện ích mở rộng — Không đạt → Đạt một phần
- **Trước:** 3 plugin không dùng nhưng chỉ ở trạng thái **tắt**: Akismet, Hello Dolly, WP Super Cache.
- **Đã sửa:** **xóa hẳn** cả 3 (không chỉ tắt). Còn lại: WooCommerce, VNPay for WooCommerce, Two Factor — đều đang dùng.
- **Còn tồn tại:** theme Twenty Twenty-Three và Twenty Twenty-Four không dùng (theme đang chạy là Storefront). **Đề xuất** xóa hai theme này, giữ Twenty Twenty-Five làm theme dự phòng mặc định của WordPress.

### 8. Ẩn thông báo lỗi chi tiết — Đạt (có lưu ý)
- **Cách kiểm tra:** gõ địa chỉ sai; gọi thẳng các file PHP nội bộ (`/wp-includes/theme-compat/embed.php`…).
- **Kết quả:** trang 404 và lỗi 500 **không** lộ đường dẫn hệ thống (`display_errors` tắt sẵn trong image WordPress, `WP_DEBUG` tắt).
- **Lưu ý (lộ phiên bản, giúp kẻ tấn công chọn lỗ hổng):** header `X-Powered-By: PHP/8.2.31`, `Server: nginx/1.31.6`; trang 403 hiện `Apache/2.4.67 (Debian)`; thẻ `generator` ghi phiên bản WordPress/WooCommerce; `/readme.html` công khai. **Đề xuất:** `server_tokens off;` + `proxy_hide_header X-Powered-By;` trong nginx, `ServerTokens Prod`/`ServerSignature Off` cho Apache, gỡ thẻ generator, chặn `/readme.html`.

### 9. Sao lưu đã thử phục hồi — Không đạt → **Đạt**
- **Trước:** không có quy trình sao lưu nào.
- **Đã sửa:** `01-cua-hang/backup/backup.sh` (mysqldump + wp-content, kèm checksum SHA-256) và `restore-test.sh` (phục hồi vào CSDL tạm `shop_restore_test`, so sánh số sản phẩm, đơn, trang, người dùng, tồn kho, checksum nội dung, số file ảnh).
- **Kết quả thử phục hồi (02/10 15:28):** checksum OK; 40 sản phẩm, 5 đơn, 9 trang, 1 người dùng, 451 tùy chọn, 292 file ảnh — **khớp 100%**.
- **Đã dùng thật:** 19:25 cùng ngày, nhóm dùng chính bản sao lưu này để đưa CSDL về trạng thái trước khi nâng cấp WooCommerce. Site chạy lại bình thường.

### 10. Rà soát mã theo dõi bên thứ ba — Đạt
- **Cách kiểm tra:** liệt kê mọi `script/img/iframe/link` trỏ ra ngoài trên trang chủ, trang sản phẩm, trang thanh toán (tương đương tab Network của công cụ nhà phát triển).
- **Kết quả:** không có Google Analytics, Facebook Pixel, Google Fonts. Chỉ có `gmpg.org` và `api.w.org`: đây là thẻ `<link rel>` khai báo, trình duyệt **không tải** nội dung từ đó.
- **Đã sửa thêm** (theo bảng đăng ký dữ liệu): tắt ảnh đại diện Gravatar (gửi mã băm email sang Gravatar) và WooCommerce Order Attribution (cookie `sbjs_*` theo dõi nguồn truy cập).

## Kết luận

Đạt 6/10 (1, 3, 5, 9, 10 và 4 có lưu ý), đạt một phần 2/10 (6, 7), không đạt 1/10 (2) — hạng mục 8 đạt về lỗi chi tiết nhưng còn lộ phiên bản. Bốn việc ưu tiên tiếp theo: (1) đóng cổng 8081 + thêm HSTS; (2) đổi mật khẩu admin và xóa khỏi README; (3) cập nhật WooCommerce 10.9.4; (4) xóa 2 theme thừa và ẩn phiên bản phần mềm.
