# Bài thực hành 7 — Đánh giá rủi ro và kế hoạch an toàn (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Bảng tính: `bang-tinh-ly-thuyet-4-10.xlsx`, sheet `LT7_RuiRo`. Điểm = khả năng × tác động, xếp hạng bằng `RANK`; ô vàng đổi được. Minh chứng kiểm tra:

- `05-buoi-12/minh-chung/10-audit-truoc-khi-sua-lan2.txt`: **trước** khi sửa (15:22)
- `05-buoi-12/minh-chung/14-audit-sau-khi-sua-lan2.txt`: **sau** khi sửa (16:00)
- `05-buoi-12/minh-chung/15-an-phien-ban-apache.txt`: sửa bổ sung lúc viết bài này

Script kiểm tra: `05-buoi-12/tools/audit.sh`.

## Bước 1 — Bảng đánh giá rủi ro (12 tài sản)

| Hạng | Tài sản | Lỗ hổng / mối đe doạ | K | T | Điểm | Trạng thái 11/10 |
|---:|---|---|---:|---:|---:|---|
| 1 | Tài khoản quản trị | Mật khẩu `admin123` ghi trong README công khai | 5 | 5 | 25 | **Đã sửa**: mật khẩu 20 ký tự, bỏ khỏi README; có MFA, khoá sau 5 lần sai |
| 2 | Khoá bí mật VNPay sandbox | Nằm trong lịch sử git công khai | 5 | 4 | 20 | **Đã sửa một phần**: chuyển sang `.env`. **Còn phải đổi khoá** trên cổng sandbox |
| 2 | Plugin WooCommerce 9.7.1 | Chậm vá (đã có 10.9.4) | 4 | 5 | 20 | **Chưa làm** |
| 2 | Plugin VNPay | Tin phản hồi trình duyệt; so chữ ký bằng `==` | 4 | 5 | 20 | **Đã sửa**: chỉ IPN đổi trạng thái; 6/6 kiểm thử đạt |
| 5 | Khoá riêng TLS | Nằm trong lịch sử git | 5 | 3 | 15 | **Đã sửa**: gỡ khỏi git, sinh khoá mới |
| 5 | Bản sao lưu | Không mã hoá, cùng máy, không ngoại tuyến | 3 | 5 | 15 | Đã thử phục hồi (3 giây). **Còn thiếu** bản ngoại tuyến có mã hoá |
| 7 | `db-init.sql` trong repo | Bản dump CSDL công khai | 4 | 3 | 12 | Đã xoá khoá, cập nhật mã băm |
| 7 | Trang quản trị | Một tài khoản dùng chung | 3 | 4 | 12 | **Chưa làm**: tài khoản riêng từng người |
| 9 | Biểu mẫu lọc sản phẩm | Tham số GET vào truy vấn | 2 | 4 | 8 | Cần rà mã `product-filters.php` |
| 9 | Máy chủ mùa tựu trường | Quá tải / từ chối dịch vụ | 2 | 4 | 8 | Có nén, lưu đệm; CDN khi triển khai thật |
| 11 | Nhật ký wc-logs | Từng lên git | 2 | 3 | 6 | Đã gỡ khỏi git, web trả 403 |
| 11 | Email khách | Giả danh (thiếu SPF/DKIM) | 2 | 3 | 6 | Khi có tên miền thật |

Lý do chấm từng ô nằm ở cột G của sheet. Nguyên tắc chấm:

- **Khả năng:** cao khi lỗ hổng phổ biến và bị công cụ dò tự động nhắm tới, hoặc thông tin đã công khai.
- **Tác động:** cao khi mất tiền, mất dữ liệu khách hoặc phát sinh nghĩa vụ pháp lý.

## Bước 2 — Kiểm tra 7 hạng mục (trước / sau)

| # | Hạng mục | Trước (minh chứng 10) | Sau (minh chứng 14, 15) |
|---|---|---|---|
| 1 | Chứng chỉ và HTTPS | HTTPS toàn site, nhưng cổng 8081 vào thẳng WordPress qua HTTP (302); khoá riêng có trong git | Cổng 8081 → 404; chứng chỉ mới cho `butchixanh.local` và `*.butchixanh.local`, hạn 2029 (tự ký, chỉ dùng cục bộ) |
| 2 | Đăng nhập | Khoá sau 5 lần sai đã hoạt động; nhưng `/?author=1` lộ tên `admin`, `/wp-json/wp/v2/users` trả danh sách người dùng, `xmlrpc.php` mở (200) | Cả ba bị chặn: author 404, users 404, xmlrpc 403 |
| 3 | Tiêu đề bảo mật | Không có HSTS, CSP, X-Frame-Options | Đủ 6 tiêu đề: HSTS, CSP (`frame-ancestors`, `base-uri`, `object-src`), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy |
| 4 | Thuộc tính cookie | Cookie giỏ hàng thiếu `Secure`, mọi cookie thiếu `SameSite` | Mọi cookie có `Secure; SameSite=Lax` |
| 5 | Lộ thông tin hệ thống | Lộ thẻ generator (WordPress 6.9.9, WooCommerce 9.7.1); `readme.html`, `license.txt` truy cập được; trang 403 in `Apache/2.4.67 (Debian)` | Generator đã gỡ; readme/license 404; chữ ký Apache tắt (`apache-an-phien-ban.conf`) |
| 6 | Bí mật trong mã nguồn | Mật khẩu admin, khoá VNPay, mật khẩu CSDL trong repo | Chuyển sang `.env` (gitignore); có `.env.example` |
| 7 | Sao lưu / phục hồi | Có sao lưu, chưa đo thời gian phục hồi | Phục hồi thử 3 giây, khớp 100% (40 sản phẩm, 2 đơn), minh chứng 13 |

**Giới hạn:** lịch sử git cũ vẫn chứa khoá và mật khẩu cũ. Theo quyết định của nhóm, không viết lại lịch sử. Mọi bí mật cũ vì vậy **coi như đã lộ** và phải được thay; mật khẩu admin và khoá TLS đã thay, khoá VNPay còn chờ.

## Bước 3 — Biện pháp cho 3 rủi ro cao nhất

| Rủi ro | Biện pháp | Kiểm chứng hiệu quả |
|---|---|---|
| Tài khoản quản trị (25) | Mật khẩu 20 ký tự lưu ngoài repo; MFA; khoá 5 lần sai; chặn liệt kê tên đăng nhập | Đăng nhập bằng `admin123` thất bại; audit [5] khoá sau lần 5; [5b] author/users 404 |
| Khoá VNPay (20) | Nạp từ biến môi trường qua `nhom25-secrets.php`; trước khi ghi CSDL thì xoá khoá; **đổi khoá** | `grep -r <khoá cũ>` trong mã hiện tại không còn kết quả; `wp option get woocommerce_woo_vnpay_settings` trong CSDL thấy trường khoá rỗng |
| Plugin VNPay (20) | Chỉ IPN đổi trạng thái đơn; `hash_equals`; khớp số tiền; chống xử lý trùng; hết hàng thì tạm giữ | `tests/kiem-tra-ipn-vnpay.php` 6/6 đạt; thông báo chữ ký giả qua HTTP trả `97` |

Rủi ro đồng hạng 2 "WooCommerce chậm vá" **chưa xử lý**. Cách làm: sao lưu → nâng cấp trên bản sao → chạy lại kiểm thử IPN, phí ship, sự kiện → nâng cấp bản chính.

## Bước 4 — Chính sách an toàn (4 mảng)

1. **Tài khoản và mật khẩu:**
   - Mỗi người một tài khoản với quyền tối thiểu: thành viên là "Quản lý cửa hàng", chỉ một quản trị viên.
   - Mật khẩu ≥ 16 ký tự, bật MFA cho mọi tài khoản có quyền quản lý.
   - Không ghi mật khẩu trong repo, tin nhắn nhóm hay README; chỉ lưu trong `TruyCap.txt` (gitignore) hoặc trình quản lý mật khẩu.
   - Thành viên rời nhóm thì vô hiệu tài khoản ngay trong ngày.
2. **Cập nhật:**
   - Kiểm tra bản cập nhật WordPress, plugin và giao diện mỗi tuần.
   - Bản vá bảo mật cài trong vòng 7 ngày, sau khi sao lưu và thử trên bản sao.
   - Gỡ plugin và giao diện không dùng (đã gỡ 2 giao diện ngày 11/10).
3. **Sao lưu:**
   - Sao lưu CSDL hằng ngày bằng `backup/backup.sh`.
   - Mỗi tuần một bản mã hoá chép ra ổ ngoài hoặc đám mây riêng, theo quy tắc 3-2-1.
   - Thử phục hồi mỗi tháng và ghi thời gian vào minh chứng; mục tiêu phục hồi dưới 1 giờ.
4. **Bí mật và dữ liệu khách:**
   - Mọi khoá, mật khẩu nằm trong `.env`.
   - Trước mỗi commit, chạy `git diff --cached | grep -iE "secret|password|BEGIN .*PRIVATE"`.
   - Dữ liệu khách chỉ xem trong trang quản trị; không xuất CSV ra máy cá nhân trừ khi có lý do và phải xoá sau khi dùng.

## Bước 5 — Quy trình ứng phó sự cố (4 giai đoạn)

| Giai đoạn | Việc làm | Người chịu trách nhiệm |
|---|---|---|
| 1. Phát hiện và đánh giá | Ghi thời điểm, dấu hiệu (đơn lạ, quản trị viên lạ, trang bị đổi). Xác định có dữ liệu cá nhân bị ảnh hưởng không | Người phát hiện báo trưởng nhóm trong 1 giờ |
| 2. Ngăn chặn — **giữ chứng cứ trước** | **Trước khi đổi bất cứ thứ gì:** chụp nhanh CSDL (`backup.sh`), sao chép `wc-logs`, nhật ký nginx (`docker compose logs nginx > ...`), danh sách người dùng và plugin kèm thời điểm. Sau đó mới đổi mật khẩu, thu hồi khoá VNPay, tắt plugin nghi ngờ, bật chế độ bảo trì | Trưởng nhóm kỹ thuật |
| 3. Khắc phục | Phục hồi từ bản sao lưu sạch gần nhất (đã thử: 3 giây); vá lỗ hổng gốc; chạy lại `audit.sh` và các kiểm thử | Trưởng nhóm kỹ thuật |
| 4. Thông báo và rút kinh nghiệm | Nếu lộ dữ liệu cá nhân: thông báo cơ quan chuyên trách và khách bị ảnh hưởng theo **Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15**. Báo cáo sự cố an ninh mạng theo **Luật An ninh mạng 116/2025/QH15**. Viết báo cáo sau sự cố, cập nhật bảng rủi ro | Trưởng nhóm + người phụ trách tuân thủ |

> **Nhóm cần tự kiểm tra:** điều khoản cụ thể và thời hạn thông báo (ví dụ 72 giờ) trong hai luật trên và văn bản hướng dẫn. Bài này chỉ ghi tên luật, chưa trích điều.

## Bước 6 — Ba ưu tiên tiếp theo

1. **Đổi khoá VNPay sandbox** trên cổng quản trị, cập nhật `.env`, chạy lại kiểm thử IPN. Khoá cũ đang công khai.
2. **Nâng cấp WooCommerce lên 10.9.4** theo quy trình ở bước 3.
3. **Tài khoản riêng cho từng thành viên và giảng viên; bản sao lưu ngoại tuyến có mã hoá.**
