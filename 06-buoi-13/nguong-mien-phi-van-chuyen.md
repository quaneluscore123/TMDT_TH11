# Căn cứ chọn ngưỡng miễn phí vận chuyển 499.000đ (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Số liệu lấy từ **dữ liệu mẫu** của học phần (`02-dataset/`), tính trong `bao-cao-buoi-13.md`.

## Vì sao bỏ ngưỡng cũ 320.000đ

| Chỉ số | Giá trị | Nguồn |
|---|---:|---|
| Giá trị đơn trung bình | 434.252đ | Báo cáo buổi 13, mục 3.2 |
| Tỷ lệ lợi nhuận gộp (có trọng số theo doanh thu) | 34,23% | Báo cáo buổi 13, mục 3.1 |
| Lợi nhuận gộp của một đơn trung bình | 434.252 × 34,23% ≈ **148.600đ** | |
| Phí vận chuyển cửa hàng phải trả khi miễn phí | 18.000–38.000đ | Bảng phí 5 vùng (`setup-shipping.php`) |
| Phần lợi nhuận gộp mất vào phí ship | 12%–26% | 18.000 ÷ 148.600 và 38.000 ÷ 148.600 |

Ngưỡng 320.000đ **thấp hơn đơn trung bình 114.000đ**, nên đơn trung bình đã được miễn phí mà không cần mua thêm gì. Ngưỡng này không làm khách tăng giá trị đơn, chỉ chuyển chi phí vận chuyển vào biên lợi nhuận. Điều này trái nguyên tắc ở lý thuyết buổi 10 (slide 9): "đặt ngưỡng cao hơn giá trị đơn trung bình một mức vừa phải".

## Vì sao chọn 499.000đ

- 499.000 ÷ 434.252 = **1,15**, tức cao hơn đơn trung bình 15%, nằm trong khoảng 10–20% mà hướng dẫn thực hành buổi 12 (bước 12.2) đề xuất.
- Khoảng cách 499.000 − 434.252 ≈ **65.000đ**, bằng giá một món nhỏ trong cửa hàng (ví dụ một bộ bút gel hoặc một quyển vở). Khách ở gần ngưỡng có lý do thêm một món, và trang sản phẩm hiện "Giỏ hàng của bạn còn thiếu …đ" để nhắc điều này.
- Mốc 499.000đ dễ nhớ và dễ truyền thông hơn một con số lẻ.

## Giới hạn

- Dữ liệu mẫu chỉ có doanh thu theo khách và theo ngày, **không có phân phối giá trị từng đơn**. Vì vậy chưa tính được bao nhiêu phần trăm đơn sẽ vượt ngưỡng mới. Khi cửa hàng có dữ liệu thật, cần đo tỷ lệ đơn trong khoảng 430.000–499.000đ trước và sau khi đổi ngưỡng.
- Giả định phí cửa hàng trả cho đơn vị vận chuyển bằng phí niêm yết cho khách. Phí thật theo hợp đồng có thể thấp hơn.
