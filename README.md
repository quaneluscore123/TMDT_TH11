# TMDT_TH11 — Cửa hàng Bút Chì Xanh (Nhóm 25)

Bài thực hành 11: Dựng cửa hàng và kiến trúc thông tin — WordPress + WooCommerce trên Docker.

- Trang chủ: https://butchixanh.local
- Quản trị: https://butchixanh.local/wp-admin — tài khoản và mật khẩu **không ghi ở đây**; nhóm trưởng gửi riêng cho từng thành viên (file `TruyCap.txt`, không đưa lên git)
- phpMyAdmin: http://localhost:8081 (chỉ mở trên máy mình)
- Mật khẩu CSDL và khóa VNPay nằm trong `01-cua-hang/.env` (không đưa lên git)

## Yêu cầu

- Docker Desktop đã cài và đang chạy (kèm `docker compose`)

## Các bước chạy

1. **Clone repo**

   ```powershell
   git clone https://github.com/quaneluscore123/TMDT_TH11.git
   cd TMDT_TH11
   ```

2. **Thêm dòng sau vào file hosts** (quản trị viên):

   - Windows: `C:\Windows\System32\drivers\etc\hosts`
   - macOS/Linux: `/etc/hosts`

   ```
   127.0.0.1 butchixanh.local
   ```

3. **Tạo file bí mật và chứng chỉ** (chỉ làm một lần, trong thư mục `01-cua-hang`):

   ```bash
   cd 01-cua-hang
   cp .env.example .env          # rồi mở .env, điền mật khẩu CSDL và khóa VNPay sandbox
   bash certs/tao-chung-chi.sh   # sinh chứng chỉ tự ký cho butchixanh.local (cần openssl; Git Bash có sẵn)
   ```

4. **Chạy cửa hàng** (bắt buộc vào thư mục `01-cua-hang`):

   ```powershell
   docker compose up -d
   ```

   Lần đầu tiên MySQL sẽ tự nạp dữ liệu từ `db-init.sql` (40 sản phẩm, trang, đơn hàng mẫu) — chờ khoảng 1–2 phút.

5. **Mở trình duyệt**: https://butchixanh.local
   - Cảnh báo SSL là bình thường (chứng chỉ tự ký) → chọn *Nâng cao → Tiếp tục truy cập (không an toàn)*.
   - Đăng nhập quản trị bằng tài khoản nhóm trưởng gửi riêng.
   - Buổi 12: plugin Two Factor đã cài. Mỗi máy tự bật xác thực 2 lớp: *Người dùng → Hồ sơ → Two-Factor Options → Authenticator App* (khóa 2FA không được đưa lên git).

## Cấu trúc thư mục

| Thư mục | Nội dung |
|---|---|
| `01-cua-hang/` | Docker Compose, nginx, chứng chỉ SSL, `db-init.sql` (dữ liệu shop), `wp-content/` (plugin, mu-plugin, hình ảnh) |
| `02-dataset/` | Bộ dữ liệu CSV (`products.csv`, `woocommerce-import.csv`, ...) |
| `03-phan-tich/` | Lược đồ dữ liệu, file phân tích |
| `04-minh-chung/` | Ảnh minh chứng các trang |
| `05-buoi-12/` | Buổi 12: nhật ký kiểm thử thanh toán, biên bản rà soát an toàn, hồ sơ tuân thủ (`ho-so-tuan-thu/`), nhật ký công việc |

## Sự cố thường gặp

- **`no configuration file provided`**: bạn đang chạy ở thư mục gốc — hãy `cd 01-cua-hang` trước.
- **Không mở được `butchixanh.local`**: kiểm tra đã thêm dòng hosts ở bước 2 (bắt buộc, vì WP trỏ `siteurl` về domain này).
- **Port 80/443/8081 đã bị chiếm** (IIS, Skype...): sửa `ports` trong `docker-compose.yml`.
- **Muốn nạp lại dữ liệu mặc định** (xóa sạch mọi thay đổi):

  ```powershell
  cd 01-cua-hang
  docker compose down -v
  docker compose up -d
  ```

  (`-v` xóa volume dữ liệu — lần `up` sau `db-init.sql` sẽ tự chạy lại.)
