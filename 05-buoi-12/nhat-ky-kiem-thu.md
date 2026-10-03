# Nhật ký kiểm thử thanh toán — Bút Chì Xanh (Buổi 12 · NV1)

Môi trường: https://butchixanh.local · WooCommerce 9.7.1 · VNPay **sandbox** (`vnp_TmnCode` B06ZF5NH) · COD · VietQR.
**Không dùng thẻ thật** — chỉ dùng bộ thẻ giả lập của VNPay sandbox (ngân hàng NCB).

## 0. Cấu hình đã kiểm tra trước khi thử

| Hạng mục | Cấu hình | Kiểm tra |
|---|---|---|
| Cổng thanh toán | COD, VietQR, VNPay sandbox — `setup-payments.php` | Trang thanh toán hiện đủ 3 phương thức |
| Phí vận chuyển | `setup-shipping.php` + `mu-plugins/nhom25-shipping.php`: **Nội thành Hà Nội** 15.000đ (đơn ≤ 1 kg) / 20.000đ (> 1 kg); **Các tỉnh khác** 20.000đ / 25.000đ | Giỏ 1 bút gel (0,77 kg) giao Hà Nội → 15.000đ; giao Đà Nẵng → 20.000đ; 2 bút (1,53 kg) giao Đà Nẵng → 25.000đ |
| Miễn phí vận chuyển | Đơn từ **320.000đ** | Giỏ 1 bút chì HB (290.000đ) → vẫn tính phí; thêm 1 bút gel (tổng 348.000đ) → chỉ còn "Miễn phí vận chuyển" |
| Hiện đủ chi phí ở trang sản phẩm | `mu-plugins/product-info-hooks.php`: bảng phí theo vùng cho chính sản phẩm đó, ngưỡng miễn phí, số tiền còn thiếu | Mở trang sản phẩm bất kỳ |
| Tồn kho | Bật quản lý tồn kho cho 40 sản phẩm theo `02-dataset/products.csv` | Trang sản phẩm hiện "Còn … hàng" |

## 1. Thẻ thử nghiệm VNPay sandbox

| Dùng cho | Số thẻ | Tên chủ thẻ | Ngày phát hành | OTP |
|---|---|---|---|---|
| Thành công | 9704198526191432198 | NGUYEN VAN A | 07/15 | 123456 |
| Thẻ bị khóa (bị từ chối) | 9704193370791314 | NGUYEN VAN A | 07/15 | 123456 |
| Không đủ số dư (hết hạn mức) | 9704195798459170488 | NGUYEN VAN A | 07/15 | 123456 |

*(Nguồn: trang hướng dẫn sandbox của VNPay. Nếu thẻ nào bị sandbox báo không hợp lệ, đối chiếu lại danh sách trên trang hướng dẫn rồi sửa bảng này.)*

## 2. Sáu trường hợp

Cột **Kết quả quan sát** và **Ảnh** điền sau khi thử. Ảnh lưu vào `minh-chung/` theo tên gợi ý.

| # | Trường hợp | Các bước | Kết quả mong đợi | Kết quả quan sát | Đạt? | Ảnh |
|---|---|---|---|---|---|---|
| 1 | **Thanh toán thành công** | Ghi tồn kho bút gel → thêm 2 bút gel vào giỏ → Thanh toán → chọn VNPay → thẻ "Thành công" → OTP 123456 | Về trang "Đã nhận đơn"; đơn ở trạng thái **Đang xử lý**; tồn kho giảm đúng 2; email xác nhận được gửi (xem Admin → WooCommerce → Trạng thái → Nhật ký / ghi chú đơn) | | | `tc1-thanh-cong.png`, `tc1-don-hang-admin.png` |
| 2 | **Bị từ chối** | Thêm sản phẩm → VNPay → thẻ "bị khóa" | Đơn **không** thành "Đang xử lý" (Thất bại); khách thấy thông báo rõ lý do và cách thử lại | | | `tc2-bi-tu-choi.png` |
| 3 | **Hết hạn mức** | Thêm sản phẩm → VNPay → thẻ "không đủ số dư" → quay lại cửa hàng → chọn COD đặt lại | Giỏ hàng còn nguyên; được gợi ý phương thức khác; đặt lại bằng COD thành công | | | `tc3-het-han-muc.png`, `tc3-doi-sang-cod.png` |
| 4 | **Đóng trang giữa lúc trả** | Thêm sản phẩm → VNPay → **đóng tab** ở trang VNPay → mở lại `/cart/` | Giỏ hàng còn nguyên; đơn ở trạng thái "Chờ thanh toán"; sau 60 phút đơn tự hủy và trả lại hàng giữ chỗ | | | `tc4-gio-con-nguyen.png` |
| 5 | **Kết quả tới muộn / tới nhiều lần** | Thanh toán thành công → ở trang kết quả bấm **F5** 2–3 lần (VNPay gửi lại kết quả) → mở ghi chú đơn trong admin | Chỉ **1** đơn, tồn kho chỉ trừ 1 lần, chỉ 1 ghi chú "thanh toán thành công" | | | `tc5-ghi-chu-don.png` |
| 6 | **Hết hàng khi đang trả** | Admin đặt tồn kho bút chì HB = 1 → khách thêm 1 vào giỏ → VNPay, **dừng ở trang nhập thẻ** → Admin sửa tồn kho = 0 → khách nhập thẻ "Thành công" | Không nhận tiền cho hàng không còn: đơn không được giao, ghi chú cần hoàn tiền, tồn kho không âm | | | `tc6-het-hang.png` |

## 3. Rủi ro đã thấy khi đọc mã plugin `woo-vnpay` (cần đối chiếu khi thử)

Ghi lại để khi thử biết chỗ cần quan sát kỹ — **chưa sửa** trong buổi này:

| Trường hợp | Mã plugin hiện tại | Hệ quả có thể quan sát |
|---|---|---|
| 2, 3 | `class-woo-vnpay-response.php` gọi `empty_cart()` cả khi thanh toán **thất bại** | Giỏ hàng bị xóa sau khi thẻ bị từ chối → trái yêu cầu "giữ nguyên giỏ" |
| 2, 3 | Khi thất bại chỉ chuyển về trang đơn hàng, không có thông báo lý do (mã 12, 51…) | Thông báo không rõ, không gợi ý phương thức khác |
| 5 | Mã tham chiếu `vnp_TxnRef` = mã đơn; kiểm tra "đơn đã ở trạng thái Đang xử lý" trước khi ghi nhận | Thông báo lặp cho **cùng** đơn bị chặn (trả `02`), nhưng thử lại lần 2 cho cùng đơn sẽ bị VNPay từ chối vì trùng `vnp_TxnRef` |
| 6 | Gọi `payment_complete()` không kiểm tra tồn kho | Đơn chuyển "Đang xử lý" và tồn kho có thể **âm** dù hàng đã hết |
| Chung | Không kiểm tra số tiền VNPay trả về khớp tổng đơn; `vnp_CreateDate` dùng giờ UTC (VNPay dùng GMT+7) | Cần ghi lại nếu sandbox báo lỗi thời gian |

## 4. Kết luận

*(Điền sau khi thử: bao nhiêu luồng đạt, luồng nào không đạt, nguyên nhân, đề xuất sửa.)*
