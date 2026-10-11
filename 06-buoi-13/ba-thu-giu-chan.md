# Ba thư giữ chân — Bút Chì Xanh

**Danh sách gửi:** chỉ khách **đã đồng ý nhận thư**. Trong dữ liệu là 139/300 khách (46%) có `da_dang_ky_thu = Co`.
**Căn cứ thời điểm:** chu kỳ mua trung bình = 365 ngày / 4,45 đơn ≈ **82 ngày**; 24% khách (71/300) mới mua đúng 1 đơn.
**Mỗi thư có nhóm đối chứng:** chọn ngẫu nhiên 10% khách đủ điều kiện **không gửi**, để đo phần tăng thêm thật do thư tạo ra, chứ không đếm cả những người vốn sẽ mua.

---

## Thư 1 — Chào mừng và gợi ý đơn thứ hai

| | |
|---|---|
| **Điều kiện kích hoạt** | **Thời gian:** 5 ngày sau khi đơn **đầu tiên** chuyển "Hoàn thành" (đã giao). **Hành vi:** chưa có đơn thứ hai, và chưa hủy đăng ký. Mỗi khách nhận tối đa 1 lần. |
| **Điều kiện viết bằng sự kiện** | Có sự kiện `purchase` (đơn A) cách đây **5 ngày**, đơn A ở trạng thái Hoàn thành, **và** không có `purchase` nào khác của cùng email khách (nối `order_id` → email người mua) trong khoảng từ đơn A tới nay, **và** email chưa nằm trong danh sách hủy đăng ký. |
| **Tiêu đề** | "Bé dùng [tên sản phẩm] thế nào rồi? Gợi ý cho góc học tập" |
| **Nội dung chính** | Cảm ơn và hỏi thăm về sản phẩm đã mua. 3 sản phẩm **cùng nhóm hàng** khách đã mua (ví dụ mua bút gel → gợi ý ruột bút, vở ô ly). Nhắc miễn phí vận chuyển cho đơn từ 499.000đ. Nút "Đánh giá sản phẩm". |
| **Chỉ số đánh giá** | **Chính:** tỷ lệ đặt đơn thứ hai trong 30 ngày, nhóm nhận thư so với nhóm đối chứng. Phụ: tỷ lệ mở, tỷ lệ nhấp, số đánh giá sản phẩm. |
| **Vì sao** | Từ 1 đơn lên 2 đơn là bước khó nhất: 76% khách mua lại, nhưng 24% dừng ở đơn đầu. |

## Thư 2 — Nhắc mua lại theo chu kỳ

| | |
|---|---|
| **Điều kiện kích hoạt** | **Thời gian:** ngày thứ 70 kể từ đơn gần nhất (trước chu kỳ 82 ngày khoảng 2 tuần). **Hành vi:** chưa mua lại, **và** đơn gần nhất có hàng tiêu hao (Bút viết, Vở & sổ, Mỹ thuật). Khách mua ba lô/túi không nhận thư này. |
| **Điều kiện viết bằng sự kiện** | `purchase` gần nhất của email khách cách đây đúng **70 ngày**, **và** không có `purchase` mới hơn của email đó, **và** đơn đó có sản phẩm thuộc nhóm Bút viết, Vở & sổ hoặc Mỹ thuật. |
| **Tiêu đề** | "Sắp hết vở/bút chưa? Đặt lại đúng món cũ chỉ một bấm" |
| **Nội dung chính** | Danh sách sản phẩm của đơn trước, kèm nút **"Đặt lại đơn này"** (tạo giỏ hàng sẵn). Còn tồn kho bao nhiêu, phí giao hàng dự kiến. **Không** giảm giá, vì khách này vẫn đang mua đều. |
| **Chỉ số đánh giá** | **Chính:** tỷ lệ mua lại trong 14 ngày, so với nhóm đối chứng. Phụ: doanh thu trên mỗi thư gửi, khoảng cách giữa hai đơn (mục tiêu rút ngắn dưới 82 ngày). |

## Thư 3 — Gọi khách quay lại

| | |
|---|---|
| **Điều kiện kích hoạt** | **Thời gian:** 150 ngày không mua (≈ 2 chu kỳ). **Hành vi:** không mở thư nào trong 60 ngày gần nhất. **Ngoại lệ mùa vụ:** khách 100–150 ngày không mua nhận thư sớm vào ngày 01/08 (đợt tựu trường). Nếu không phản hồi sau thư này và một lần nhắc 7 ngày sau, ngừng gửi thư khuyến mãi để tránh bị coi là thư rác. |
| **Điều kiện viết bằng sự kiện** | `purchase` gần nhất của email khách cách đây **≥ 150 ngày**, **và** không có `purchase` mới hơn của email đó, **và** email chưa hủy đăng ký. (Điều kiện "không mở thư 60 ngày" lấy từ thống kê của dịch vụ gửi thư, vì bảng sự kiện không gắn phiên với từng khách.) |
| **Tiêu đề** | "Mùa tựu trường: mã giảm 10% cho danh sách đồ dùng của bé" |
| **Nội dung chính** | Danh sách đồ dùng theo khối lớp (dùng thuộc tính `khoi_lop` của sản phẩm). **Mã giảm 10%, hạn 14 ngày** (lý do để quay lại ngay). Liên kết "Không muốn nhận thư nữa? Hủy tại đây" đặt rõ ở đầu thư. |
| **Chỉ số đánh giá** | **Chính:** tỷ lệ khách quay lại mua trong 30 ngày. **Bảo vệ:** tỷ lệ hủy đăng ký và báo thư rác (< 0,5%), lợi nhuận gộp sau khi trừ giảm giá (mã 10% không được làm lỗ: biên gộp 34,2%). |

---

## Ràng buộc pháp lý (ghi rõ trong bài)

**Cơ sở của sự đồng ý nhận thư**
- Gửi thư quảng cáo **chỉ dựa trên sự đồng ý** của khách (Luật Bảo vệ dữ liệu cá nhân; quy định chống thư điện tử rác).
- Lấy đồng ý bằng ô "Tôi muốn nhận ưu đãi và gợi ý qua email" ở trang thanh toán. Ô này **không đánh dấu sẵn** và **không bắt buộc** để đặt hàng.
- Lưu bằng chứng đồng ý: thời điểm, nội dung câu đồng ý, nguồn (trang thanh toán).
- Thư xác nhận và vận chuyển đơn hàng dựa trên hợp đồng mua bán, không cần đồng ý, nhưng **không được chèn quảng cáo** vào các thư này.

**Cơ chế hủy đăng ký**
- Mọi thư đều có liên kết "Hủy nhận thư" **một bấm, không cần đăng nhập**.
- Ngừng gửi ngay; chậm nhất trong **24 giờ**.
- Khách đã hủy bị loại khỏi cả 3 luồng; xóa khỏi danh sách gửi nhưng giữ bản ghi "đã hủy" để không gửi lại nhầm.

**Việc phải làm trước khi gửi:** website hiện **chưa có** ô đồng ý nhận thư, và Chính sách quyền riêng tư cùng Bảng đăng ký dữ liệu (buổi 12) đang ghi "không có biểu mẫu đăng ký nhận tin". Phải thêm ô đồng ý và bổ sung một dòng trong Bảng đăng ký dữ liệu:

| Tên trường | Mục đích | Cơ sở | Thời gian lưu | Chia sẻ với ai |
|---|---|---|---|---|
| Email marketing + thời điểm đồng ý | Gửi 3 thư giữ chân | Đồng ý | Đến khi hủy | Dịch vụ gửi thư (nếu có) |
