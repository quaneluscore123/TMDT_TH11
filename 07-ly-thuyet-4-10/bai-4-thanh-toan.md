# Bài thực hành 4 — Cấu hình, kiểm thử và đối soát thanh toán (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Bảng tính: `bang-tinh-ly-thuyet-4-10.xlsx` (sheet `LT4_*`). Chỉ dùng môi trường thử nghiệm; không dùng thẻ hay tài khoản ngân hàng thật.

## Bước 1 — Ba phương thức và chi phí

| Phương thức | Mô tả hiển thị ở trang thanh toán (rút gọn) | Cơ chế |
|---|---|---|
| Chuyển khoản VietQR | Quét mã bằng ứng dụng ngân hàng; giữ nội dung **DH&lt;mã đơn&gt;**; cửa hàng xác nhận sau khi đối chiếu sao kê | Dựa trên tài khoản ngân hàng, phí gần bằng 0 |
| Thanh toán khi nhận hàng (COD) | Trả tiền mặt khi nhận, được kiểm tra ngoại quan; cửa hàng gọi xác nhận trước khi gửi | Đơn vị vận chuyển thu hộ |
| VNPAY (thẻ, ví) | Nhập thẻ trên trang VNPAY, cửa hàng không nhận và không lưu số thẻ; đơn chỉ xác nhận khi VNPAY báo đã nhận tiền | Cổng thanh toán trung gian |

Cấu hình: `01-cua-hang/setup-payments.php`, `mu-plugins/vietqr-payment-gateway.php`. Ảnh trang thanh toán đủ 3 phương thức: `04-minh-chung/14-trang-thanh-toan-3-phuong-thuc.png` (nhóm chụp lại sau khi mô tả mới đã lên site).

**Bảng chi phí ba mức đơn** (sheet `LT4_ChiPhi`, công thức `phí = phí cố định + tỷ lệ × giá trị đơn`, `tỷ trọng = phí ÷ lợi nhuận gộp`, biên gộp 34,23%):

| | Đơn 150.000đ | Đơn 450.000đ | Đơn 1.500.000đ |
|---|---:|---:|---:|
| Lợi nhuận gộp | 51.345đ | 154.035đ | 513.450đ |
| VNPay (1.650đ + 1,1%) | 3.300đ · **6,4%** | 6.600đ · 4,3% | 18.150đ · 3,5% |
| VietQR (0đ) | 0đ · 0% | 0đ · 0% | 0đ · 0% |
| COD (0,8%) | 1.200đ · 2,3% | 3.600đ · 2,3% | 12.000đ · 2,3% |

**Giả định phải ghi rõ:** biểu phí VNPay và COD trong bảng là **giả định**, chưa tra được biểu phí chính thức. Nhóm cần tra biểu phí của VNPAY và đơn vị vận chuyển, ghi nguồn và ngày tra vào cột D của sheet; bảng tự tính lại. Nhận xét: với đơn 150.000đ, phần phí cố định 1.650đ của VNPay làm tỷ trọng gần gấp đôi đơn 1,5 triệu. Đơn nhỏ nên ưu tiên VietQR. COD có thêm chi phí ẩn chưa nằm trong bảng: chiếm dụng vốn, hoàn hàng, đối chiếu bảng kê thu hộ.

## Bước 2 — Kết nối môi trường thử nghiệm

- **Khoá:** mã `vnp_TmnCode` và `vnp_HashSecret` của sandbox nằm trong `01-cua-hang/.env` (không lên git), được nạp qua `mu-plugins/nhom25-secrets.php`. Khoá không có trong mã nguồn chạy phía trình duyệt, trong CSDL hay trong repo. Khoá cũ đã từng lộ trên GitHub nên **phải đổi khoá mới** trên cổng sandbox.
- **Địa chỉ trả về cho người dùng (ReturnUrl):** `https://butchixanh.local/wp-admin/admin-ajax.php?action=vnpay_response`. Chỉ kiểm tra chữ ký rồi chuyển hướng, **không đổi trạng thái đơn**. Thất bại thì quay lại trang thanh toán, giỏ còn nguyên.
- **Địa chỉ nhận thông báo máy chủ tới máy chủ (IPN):** `https://butchixanh.local/?wc-api=nhom25_vnpay_ipn`. Đây là nơi **duy nhất** ghi nhận thanh toán.

**Thông điệp gửi đi** (tạo trong `class-woo-vnpay-payment-gateway.php`): `vnp_Version=2.1.0`, `vnp_Command=pay`, `vnp_TmnCode`, `vnp_Amount` (= tổng đơn × 100), `vnp_CurrCode=VND`, `vnp_TxnRef` (= `<mã đơn>-<thời điểm>`, riêng cho mỗi lần thử), `vnp_OrderInfo`, `vnp_CreateDate` và `vnp_ExpireDate` (giờ Việt Nam, hạn 15 phút), `vnp_IpAddr`, `vnp_ReturnUrl`, cuối cùng là `vnp_SecureHash` = HMAC-SHA512 của chuỗi tham số đã sắp xếp, ký bằng `vnp_HashSecret`.

**Thông điệp nhận về (IPN):** các tham số `vnp_*` gồm `vnp_ResponseCode`, `vnp_TransactionStatus`, `vnp_TransactionNo`, `vnp_Amount`, `vnp_TxnRef` và `vnp_SecureHash`. Cửa hàng trả JSON `{"RspCode": ..., "Message": ...}`:

| Kiểm tra theo thứ tự | Không đạt thì trả |
|---|---|
| Chữ ký HMAC khớp (so bằng `hash_equals`) | `97` Invalid signature |
| Tìm thấy đơn từ `vnp_TxnRef` | `01` Order not found |
| `vnp_Amount` = tổng đơn × 100 | `04` Invalid amount |
| Đơn còn ở trạng thái chờ thanh toán (chống xử lý trùng) | `02` Order already confirmed |
| Đủ tồn kho; nếu hết hàng thì giữ đơn "Tạm giữ" để hoàn tiền, không trừ kho âm | `00` (đã nhận tiền) |

**Hạn chế của môi trường cục bộ:** VNPay không gọi được tới `butchixanh.local` nên IPN thật không tới. Nhóm kiểm thử IPN bằng `01-cua-hang/tests/kiem-tra-ipn-vnpay.php`: script tự ký thông báo bằng khoá sandbox, giống hệt cách VNPay ký. Khi triển khai thật cần tên miền công khai, hoặc tạm thời mở đường hầm (ví dụ Cloudflare Tunnel) rồi khai báo địa chỉ IPN trong trang quản trị sandbox.

## Bước 3 — Năm kịch bản

| KB | Thao tác | Kết quả quan sát | Đánh giá |
|---|---|---|---|
| 1 | Thanh toán thành công | IPN hợp lệ → đơn **Đang xử lý**, tồn kho 97 → 96 (minh chứng 12, dòng 4) | Đạt |
| 2 | Thẻ bị từ chối | IPN mã 11 → đơn **Thất bại**, tồn kho không đổi (dòng 3); khách quay lại trang thanh toán, giỏ còn nguyên | Đạt |
| 3 | Đóng trình duyệt ngay sau khi xác nhận | Trạng thái đơn **chỉ đổi khi IPN tới**, không phụ thuộc trình duyệt. Trang trả về giả mạo (chữ ký sai) bị từ chối (`97`, dòng 1) | Đạt. Trước khi sửa thì **không đạt**: plugin gốc đổi trạng thái ngay ở trang cảm ơn theo tham số trình duyệt |
| 4 | Bấm thanh toán / VNPay gửi thông báo hai lần | Lần hai trả `02`, tồn kho không trừ lần hai (dòng 5) | Đạt |
| 5 | Hoàn tiền một phần | Sandbox VNPay có API hoàn tiền (`vnp_Command=refund`) nhưng plugin chưa tích hợp. Ghi nhận là hạn chế; hoàn tiền thủ công trong WooCommerce chỉ ghi sổ, không gọi cổng | Chưa thực hiện được |

Minh chứng: `05-buoi-12/minh-chung/12-kiem-thu-ipn-vnpay.txt` (6/6 trường hợp đạt, kèm thời điểm chạy). **Nhóm cần làm thêm trên trình duyệt** với thẻ sandbox NCB (`05-buoi-12/nhat-ky-kiem-thu.md` mục 1) và chụp ảnh từng kịch bản.

## Bước 4 — Đối soát và quy tắc rủi ro

**Bảng đối soát ba nguồn** (sheet `LT4_DoiSoat`): cột mã đơn, số tiền theo cửa hàng, theo cổng, phí cổng, thực nhận theo sao kê, hai cột chênh lệch tính bằng công thức, cột kết luận tự động "Khớp/LỆCH". Dữ liệu là **giả lập** vì sandbox không có sao kê thật. Có 4 dòng lệch, mỗi dòng ghi nguyên nhân và hướng xử lý:

- DH202: giao dịch trùng
- DH203: lệch số tiền do mã giảm giá áp sau khi cấp phép
- Một khoản VietQR có tiền nhưng không có đơn
- DH204: có đơn nhưng chưa có tiền

**Ba quy tắc chấm điểm rủi ro** (sheet `LT4_QuyTacRuiRo`, ngưỡng là ô vàng đổi được):

1. Đơn đầu tiên của một email lớn hơn **3 lần** đơn trung bình (≈ 1,3 triệu) → **giữ đơn**, gọi xác nhận trước khi giao.
2. Cùng một IP có **≥ 3** lần thanh toán bị từ chối trong **1 giờ** → **chặn** thanh toán thẻ của IP đó 1 giờ (vẫn cho COD).
3. Đơn COD giao ngoài Hà Nội có giá trị **≥ 1.000.000đ** → **xác minh** qua điện thoại trước khi gửi.

## Điểm cộng — kiểm tra chữ ký và thông báo giả mạo

Hàm `Nhom25_VNPay_IPN::verify()` tính lại HMAC-SHA512 và so bằng `hash_equals`. Script kiểm thử gửi một thông báo do nhóm tự ký bằng khoá sai: hệ thống trả `97 Invalid signature`, đơn vẫn chờ thanh toán, tồn kho không đổi. Gửi trực tiếp qua HTTP cũng bị từ chối:

```
curl -sk "https://butchixanh.local/?wc-api=nhom25_vnpay_ipn&vnp_TxnRef=11-1&vnp_Amount=100&vnp_ResponseCode=00&vnp_SecureHash=deadbeef"
{"RspCode":"97","Message":"Invalid signature"}
```
