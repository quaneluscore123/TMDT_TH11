# Nhật ký công việc — Sửa theo checklist lý thuyết 4–15 (Nhóm 25)

Ngày 11/10/2026, nhánh `sua-theo-checklist` (**chưa push**). Thực hiện cùng trợ lý AI Claude Code; nhóm rà soát và chịu trách nhiệm nội dung. Cột **Người rà soát** do nhóm điền.

| # | Việc | Commit | Kiểm chứng | Người rà soát |
|---|---|---|---|---|
| 1 | Đưa bí mật ra `.env` (mật khẩu CSDL, khoá VNPay, tài khoản VietQR); gỡ khoá TLS khỏi git, sinh khoá mới; đóng cổng 8081; thêm tiêu đề bảo mật, cờ cookie; đổi mật khẩu admin (lưu `TruyCap.txt`, không lên git) | 3ad11cc | Minh chứng 10 → 14 | |
| 2 | Vận chuyển 5 vùng, miễn phí từ 499.000đ; cập nhật Điều khoản, Chính sách đổi trả, đăng lại | 1f95359 | Minh chứng 11 | |
| 3 | VNPay: chỉ IPN đổi trạng thái đơn, `hash_equals`, khớp số tiền, chống trùng, hết hàng thì tạm giữ; thất bại giữ giỏ. VietQR tạo mã theo đơn | c29258d | Minh chứng 12 (6/6) | |
| 4 | Trang chủ, chân trang: câu định vị, thông tin người bán (ghi rõ dữ liệu mẫu); bỏ "Giá tốt nhất", "Giao 24h" | 8697652 | Mở trang chủ | |
| 5 | Plugin ghi 5 sự kiện phễu bên thứ nhất + trang "Phễu Nhóm 25"; link UTM; điều kiện thư giữ chân bằng sự kiện; cập nhật chính sách dữ liệu | b009bb2 | `06-buoi-13/minh-chung-13-1-kiem-chung-su-kien.txt` | |
| 6 | Sao lưu ghi thời gian phục hồi; xoá 2 giao diện không dùng | 071b64b | Minh chứng 13 (3 giây) | |
| 7 | SEO: noindex,follow cho URL lọc/sắp xếp, canonical, mô tả riêng từng trang | f579c9a | Bài 5, bước 1 | |
| 8 | Ẩn thẻ generator, chặn liệt kê tác giả; audit sau khi sửa | b47bd0d | Minh chứng 14 | |
| 9 | Tắt chữ ký Apache trên trang lỗi (`apache-an-phien-ban.conf`); xoá trang mẫu và "Hello world" (thùng rác); bảng tính và báo cáo bài 4–10 | (commit này) | Minh chứng 15; bài 5 | |

## Vấn đề gặp phải

| Vấn đề | Cách xử lý |
|---|---|
| Kiểm thử IPN trường hợp hết hàng làm tồn kho thành −1 (trạng thái Tạm giữ cũng trừ kho) | Đánh dấu đã trừ kho trước khi đổi trạng thái |
| Số đơn trong CSDL giảm từ 5 (02/10) xuống còn vài đơn trước khi bắt đầu, không do phiên này | Nhóm kiểm tra cài đặt tự xoá đơn của WooCommerce |
| Lịch sử git còn khoá và mật khẩu cũ (nhóm chọn không viết lại lịch sử) | Coi mọi bí mật cũ là đã lộ; đã thay mật khẩu admin và khoá TLS; **khoá VNPay còn phải đổi** |

## Việc nhóm cần làm (không làm được thay)

1. **Đổi khoá VNPay sandbox** trên cổng quản trị → cập nhật `.env` → chạy `tests/kiem-tra-ipn-vnpay.php`.
2. Lấy bộ tài liệu thực hành `buoi11-15` (mock gateway, checklist 6 trang, TH1–TH4) nếu giảng viên phát.
3. Chụp ảnh:
   - Trang "Phễu Nhóm 25"
   - Trang thanh toán 3 phương thức (mô tả mới)
   - Các kịch bản VNPay trên trình duyệt với thẻ NCB
   - Giao diện ở độ rộng 375px
4. Điền số tài khoản VietQR thử nghiệm (`VIETQR_ACCOUNT_*` trong `.env`).
5. Nâng cấp WooCommerce 10.9.4 theo quy trình bài 7, bước 3.
6. Tạo tài khoản riêng cho từng thành viên (Quản lý cửa hàng) và cho giảng viên; ghi vào `TruyCap.txt`.
7. Chép bản sao lưu có mã hoá ra ổ ngoài; sau khi đổi khoá VNPay thì xoá các bản sao lưu cũ trong `backup/bak-*` (còn chứa khoá cũ).
8. Thêm xuất xứ, thương hiệu cho 40 sản phẩm bằng dữ liệu thật (bài 8, P1).
9. Quyết định cookie đo lường: giữ lợi ích chính đáng hay thêm hộp đồng ý (bài 8, P3).
10. Tra biểu phí VNPay, COD và CPC thật; điền vào ô vàng của bảng tính, ghi nguồn và ngày tra.
11. Đối chiếu số điều luật trong bài 7, 8.
12. Bài 9 (đo đạc, phác thảo thiết kế lại); seminar TH14; báo cáo và demo TH15.
13. Điền nhật ký buổi 11, 13 và cột "Người thực hiện" buổi 12.
