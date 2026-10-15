# Bài thực hành 10 — Logistics và ứng dụng AI (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Bảng tính: sheet `LT10_Logistics` (công thức liên kết với `GiaDinh`). Cấu hình vận chuyển: `01-cua-hang/setup-shipping.php`, `mu-plugins/nhom25-shipping.php`. Lý do chọn ngưỡng: `06-buoi-13/nguong-mien-phi-van-chuyen.md`.

## Bước 1 — Chỉ số logistics

| Chỉ số | Cách đo | Hiện tại | Mục tiêu | Vấn đề nằm ở đâu |
|---|---|---:|---:|---|
| Thời gian xử lý đơn | Từ "Đang xử lý" tới bàn giao vận chuyển | Chưa đo | ≤ 24 giờ | Nội bộ |
| Thời gian giao | Từ bàn giao tới khách nhận | Chưa đo | ≤ 4 ngày | Đối tác vận chuyển |
| Giao thành công lần đầu | Đơn giao được ngay ÷ đơn giao | Chưa đo | ≥ 95% | Địa chỉ, gọi trước khi giao |
| Tỷ lệ hoàn (tách nguyên nhân) | Đơn hoàn ÷ đơn giao; lỗi / sai mô tả / đổi ý | 3% (giả định) | ≤ 3% | Chất lượng hàng hoặc trang sản phẩm |
| Độ chính xác đơn | Đơn đúng SP, số lượng ÷ tổng đơn | Chưa đo | ≥ 99% | Chọn và đóng gói |
| Chi phí logistics mỗi đơn | Ship cửa hàng chịu + đóng gói 5.000đ + 3% × 68.200đ | **7.046đ** | — | |
| Lợi nhuận gộp mỗi đơn | 434.252đ × 34,23% | **148.644đ** | — | |
| Logistics / lợi nhuận gộp | | **4,7%** | ≤ 30% | Vượt 100% thì bán càng nhiều càng lỗ |
| Ngưỡng miễn phí / đơn trung bình | 499.000 ÷ 434.252 | **1,15** | 1,1–1,2 | Đạt |

**Diễn giải:**

- Đơn trung bình dưới ngưỡng miễn phí nên khách tự trả ship, và chi phí logistics cửa hàng chịu chỉ 4,7% lợi nhuận gộp.
- Đơn từ 499.000đ được miễn phí, khi đó cửa hàng chịu thêm khoảng 31.600đ. Với đơn 499.000đ: (5.000 + 2.046 + 31.600) ÷ (499.000 × 34,23%) = **22,6%**, vẫn dưới mục tiêu 30%.
- Chưa tính tỉ trọng đơn vượt ngưỡng vì dữ liệu mẫu quá ít. Khi có đơn thật, thay dòng "Ship cửa hàng chịu" bằng *tỉ lệ đơn ≥ 499k × 31.600*.

**Hạn chế dữ liệu:** khối lượng trong `products.csv` rất lớn (bút gel 767 g), nên gần như mọi đơn rơi vào mức phí > 1 kg. Nhóm cần cân lại hàng thật. Phí ship hiện là ước tính theo vùng, chưa phải bảng giá hợp đồng với đơn vị vận chuyển.

Năm chỉ số "Chưa đo" cần dữ liệu vận hành thật. Cách ghi khi bắt đầu bán: thêm ghi chú đơn "Đã bàn giao — mã vận đơn …" kèm thời điểm, rồi xuất danh sách đơn hằng tuần.

## Bước 2 — Chọn mô hình kho vận

| Mô hình | Ưu | Nhược | Phù hợp khi |
|---|---|---|---|
| **Tự xử lý (kho tại nhà / cửa hàng)** | Kiểm soát chất lượng, đóng gói theo ý; không phí cố định | Tốn công; khó co giãn mùa tựu trường | < 30 đơn/ngày |
| Thuê kho dịch vụ (fulfillment / 3PL) | Co giãn theo mùa; giao nhanh hơn nhờ kho gần khách | Phí lưu kho + phí mỗi đơn; mất kiểm soát đóng gói | Mùa cao điểm hoặc > 50 đơn/ngày |
| Giao thẳng từ nhà cung cấp (dropship) | Không giữ hàng | Không kiểm soát được hàng kiểm định, thời gian giao | Không phù hợp định vị "an toàn" |

**Chọn:** **tự xử lý** ở giai đoạn hiện tại. Lý do:

1. Định vị "kiểm định an toàn" đòi hỏi tự kiểm tra từng lô hàng.
2. Lượng đơn nhỏ.
3. Chi phí logistics đang thấp (4,7%).

**Điểm chuyển:** khi đơn mùa tựu trường (tháng 7–8) vượt khoảng 50 đơn/ngày, hoặc thời gian xử lý vượt 24 giờ hai tuần liền, thì thuê kho dịch vụ cho riêng mùa cao điểm.

## Bước 3 — Ứng dụng AI: gợi ý và rủi ro

| Ứng dụng | Lợi ích | Rủi ro | Biện pháp kiểm soát |
|---|---|---|---|
| Dự báo nhu cầu theo mùa (nhập hàng trước tựu trường) | Giảm hết hàng tháng 8, giảm tồn sau mùa | Dữ liệu lịch sử quá ít nên dự báo sai | Chỉ dùng làm tham khảo; giữ tồn an toàn thủ công cho 10 sản phẩm bán chạy |
| Viết nháp mô tả sản phẩm, bài SEO (bài 5) | Nhanh có nội dung cho 13 từ khoá còn thiếu | **Bịa thông tin kiểm định, xuất xứ**: rủi ro pháp lý (bài 8, P1) và ngược định vị | Người duyệt đối chiếu với giấy tờ nhà cung cấp; cấm AI viết khẳng định an toàn |
| Trả lời tự động "đơn của tôi đâu" | Giảm tin nhắn lặp lại | Lộ thông tin đơn nếu không xác thực | Chỉ trả lời qua trang tra cứu đơn đã có (mã đơn + email) |
| Kiểm tra địa chỉ khi đặt hàng | Tăng giao thành công lần đầu | Gửi địa chỉ khách cho bên thứ ba | Ghi vào Bảng đăng ký dữ liệu; chọn nhà cung cấp lưu dữ liệu tại Việt Nam hoặc có thoả thuận xử lý dữ liệu |
| Gợi ý sản phẩm theo khối lớp | Tăng giá trị đơn lên gần ngưỡng 499k | Dùng dữ liệu hành vi cần căn cứ hợp pháp | Dựa trên giỏ hàng hiện tại (không cần hồ sơ khách) |

**Nguyên tắc chung:**

- Không đưa dữ liệu cá nhân của khách vào công cụ AI công cộng.
- Mọi nội dung AI tạo ra đều có người duyệt trước khi đăng.
- Ghi rõ trong nhật ký công việc phần nào có AI hỗ trợ.
