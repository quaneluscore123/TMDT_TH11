# TMDT_TH11 — Cửa hàng Bút Chì Xanh (Nhóm 25)

Bài thực hành 11: Dựng cửa hàng và kiến trúc thông tin — WordPress + WooCommerce trên Docker.

- Trang chủ: https://butchixanh.local
- Đăng nhập quản trị: `admin` / `admin123`
- Database: `shop` / user `shop` / pass `shop123` (root: `root123`)

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

3. **Chạy cửa hàng** (bắt buộc vào thư mục `01-cua-hang`):

   ```powershell
   cd 01-cua-hang
   docker compose up -d
   ```

   Lần đầu tiên MySQL sẽ tự nạp dữ liệu từ `db-init.sql` (40 sản phẩm, trang, đơn hàng mẫu) — chờ khoảng 1–2 phút.

4. **Mở trình duyệt**: https://butchixanh.local
   - Cảnh báo SSL là bình thường (chứng chỉ tự ký) → chọn *Nâng cao → Tiếp tục truy cập (không an toàn)*.
   - Đăng nhập admin: `admin` / `admin123`.

## Cấu trúc thư mục

| Thư mục | Nội dung |
|---|---|
| `01-cua-hang/` | Docker Compose, nginx, chứng chỉ SSL, `db-init.sql` (dữ liệu shop), `wp-content/` (plugin, mu-plugin, hình ảnh) |
| `02-dataset/` | Bộ dữ liệu CSV (`products.csv`, `woocommerce-import.csv`, ...) |
| `03-phan-tich/` | Lược đồ dữ liệu, file phân tích |
| `04-minh-chung/` | Ảnh minh chứng các trang |

## Sự cố thường gặp

- **`no configuration file provided`**: bạn đang chạy ở thư mục gốc — hãy `cd 01-cua-hang` trước.
- **Không mở được `butchixanh.local`**: kiểm tra đã thêm dòng hosts ở bước 2 (bắt buộc, vì WP trỏ `siteurl` về domain này).
- **Port 80/443/8080 đã bị chiếm** (IIS, Skype...): sửa `ports` trong `docker-compose.yml`.
- **Muốn nạp lại dữ liệu mặc định** (xóa sạch mọi thay đổi):

  ```powershell
  cd 01-cua-hang
  docker compose down -v
  docker compose up -d
  ```

  (`-v` xóa volume dữ liệu — lần `up` sau `db-init.sql` sẽ tự chạy lại.)
