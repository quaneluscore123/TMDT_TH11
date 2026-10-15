# Bài thực hành 8 — Rà soát tuân thủ pháp luật (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Hồ sơ tuân thủ: `05-buoi-12/ho-so-tuan-thu/` (4 văn bản đã đăng lên site). Thông tin người bán: `mu-plugins/nhom25-thong-tin-nguoi-ban.php`.

> **Lưu ý về căn cứ pháp lý:** số hiệu văn bản bên dưới là những văn bản nhóm đã dùng trong học phần. Văn bản về thương mại điện tử và dữ liệu cá nhân thay đổi nhiều trong 2025–2026, nên **nhóm phải tự đối chiếu số điều và hiệu lực** trên cổng văn bản pháp luật trước khi nộp. Ô "Điều" để trống nghĩa là chưa đối chiếu.

## Bước 1 — Danh sách yêu cầu

| # | Yêu cầu | Căn cứ | Điều (cần đối chiếu) |
|---|---|---|---|
| Y1 | Công khai thông tin người bán: tên, địa chỉ, MST/số ĐKKD, điện thoại, email | Nghị định 52/2013/NĐ-CP (sửa đổi bởi 85/2021/NĐ-CP) về TMĐT | Điều 29 |
| Y2 | Thông tin hàng hoá: đặc tính, xuất xứ, thương hiệu/nhà sản xuất, cảnh báo an toàn | NĐ 52/2013 và quy định về nhãn hàng hoá | Điều 30 |
| Y3 | Giá rõ ràng, đã gồm thuế hay chưa, các chi phí phát sinh (vận chuyển) | NĐ 52/2013 | Điều 31 |
| Y4 | Điều kiện giao dịch chung: hình thành hợp đồng, huỷ đơn, đổi trả, khiếu nại | NĐ 52/2013; Luật Bảo vệ quyền lợi người tiêu dùng 19/2023/QH15 | Điều 32 (NĐ 52) |
| Y5 | Vận chuyển: phạm vi, thời hạn, phí | NĐ 52/2013 | Điều 33 |
| Y6 | Phương thức thanh toán và an toàn thanh toán | NĐ 52/2013 | Điều 34 |
| Y7 | Rà soát và xác nhận đơn trước khi gửi; xác nhận sau khi đặt | NĐ 52/2013 | Điều 16–17 |
| Y8 | Chính sách bảo vệ dữ liệu cá nhân: mục đích, cơ sở xử lý, thời gian lưu, quyền chủ thể | Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15 | |
| Y9 | Cookie không thiết yếu cần đồng ý; cookie thiết yếu được công bố | Luật 91/2025/QH15 | |
| Y10 | Thông báo vi phạm dữ liệu và sự cố an ninh mạng | Luật 91/2025/QH15; Luật An ninh mạng 116/2025/QH15 | |
| Y11 | Quảng cáo trung thực, không khẳng định không chứng minh được | Luật Quảng cáo 2012 (và sửa đổi); Luật BVQLNTD 2023 | |
| Y12 | Gửi thư tiếp thị chỉ khi có đồng ý, có cách từ chối | Quy định chống thư rác; Luật 91/2025/QH15 | |

## Bước 2 — Kiểm tra công khai (thời điểm: 11/10/2026 16:15)

Phương pháp: tải trang bằng `curl` và tìm nội dung (lệnh ghi ở cột cuối). Trang sản phẩm mẫu: `/product/but-bi-gel-0-5mm/`.

| # | Yêu cầu | Kết quả | Kiểm tra |
|---|---|---|---|
| Y1 | Thông tin người bán | **Một phần.** Chân trang có tên hộ kinh doanh, điện thoại, email. Địa chỉ ghi "Hà Nội (chưa có địa chỉ đăng ký)"; MST, ĐKKD ghi "Chưa đăng ký — dữ liệu mẫu" | `curl -sk https://butchixanh.local/ \| grep "Mã số thuế"` |
| Y2 | Thông tin hàng hoá | **Thiếu.** Có kiểm định an toàn, khối lớp; **không có xuất xứ, thương hiệu/nhà sản xuất** | `grep -c "Xuất xứ"` trang sản phẩm → 0 |
| Y3 | Giá, thuế, phí | **Đạt.** Ghi "Giá niêm yết là giá cuối cùng, không cộng thêm thuế hay phí ẩn"; phí ship 5 vùng và ngưỡng miễn phí 499.000đ trong Điều khoản | `grep "giá cuối cùng"`; `grep "499.000" /dieu-khoan-ban-hang/` |
| Y4 | Điều kiện giao dịch | **Đạt.** Điều khoản 8 mục: hình thành hợp đồng (mục 2), huỷ đơn (3), đổi trả (6), giải quyết tranh chấp và khiếu nại, phản hồi 3 ngày (8) | Trang `/dieu-khoan-ban-hang/` |
| Y5 | Vận chuyển | **Đạt.** Bảng 5 vùng có phí và thời gian giao | `minh-chung/11-kiem-tra-phi-ship-5-vung.txt` |
| Y6 | Thanh toán | **Đạt.** 3 phương thức có mô tả; không lưu số thẻ; đơn chỉ xác nhận khi cổng báo đã nhận tiền | Bài 4 |
| Y7 | Rà soát và xác nhận đơn | **Đạt.** Trang thanh toán có ô đồng ý Điều khoản (trang 116); trang xác nhận đơn; trang tra cứu đơn | `wp option get woocommerce_terms_page_id` → 116 |
| Y8 | Chính sách dữ liệu | **Đạt.** Chính sách quyền riêng tư và Bảng đăng ký dữ liệu (mục đích, cơ sở, thời gian lưu, quyền) | `/chinh-sach-quyen-rieng-tu/` |
| Y9 | Cookie | **Cần quyết định.** Cookie `n25_sid`, `n25_src` đã công bố với cơ sở lợi ích chính đáng; chưa có hộp đồng ý | Bảng đăng ký dữ liệu |
| Y10 | Thông báo vi phạm | **Có quy trình** | Bài 7, bước 5 |
| Y11 | Quảng cáo trung thực | **Đạt (đã sửa).** Bỏ "Giá tốt nhất", "Giao hàng 24h" khỏi trang chủ | Commit 8697652 |
| Y12 | Thư tiếp thị | **Chưa áp dụng.** Chưa gửi thư nào; đã bỏ ô "Đăng ký nhận tin" thiếu cơ chế đồng ý | Bài 6, điểm cộng |

## Bước 3 — Phát hiện (bốn phần: hiện trạng – yêu cầu – rủi ro – khắc phục)

**P1. Trang sản phẩm thiếu xuất xứ và thương hiệu.**

- *Hiện trạng:* 40 sản phẩm chỉ có thuộc tính kiểm định, khối lớp, loại sản phẩm.
- *Yêu cầu:* Y2. Hàng cho trẻ em càng cần nguồn gốc rõ.
- *Rủi ro:* bị xử phạt khi kiểm tra; phụ huynh không tin tưởng, ngược với định vị "an toàn".
- *Khắc phục:* thêm hai thuộc tính `pa_xuat-xu`, `pa_thuong-hieu` bằng dữ liệu **thật** từ nhà cung cấp. Nhóm không tự điền vì sẽ thành thông tin sai.

**P2. Thông tin người bán là dữ liệu mẫu.**

- *Hiện trạng:* MST, ĐKKD, địa chỉ chưa có.
- *Yêu cầu:* Y1.
- *Rủi ro:* nếu mở bán thật thì vi phạm ngay, và khách không biết ai chịu trách nhiệm.
- *Khắc phục:* trước khi bán thật, đăng ký hộ kinh doanh, điền thông tin vào `nhom25_seller_info()`, thông báo website với cơ quan quản lý TMĐT. Trong học phần, giữ nhãn "dữ liệu mẫu" để không gây hiểu lầm.

**P3. Cookie đo lường dùng cơ sở lợi ích chính đáng, chưa có đồng ý.**

- *Hiện trạng:* hai cookie bên thứ nhất, dữ liệu không rời máy chủ, đã công bố.
- *Yêu cầu:* Y9.
- *Rủi ro:* nếu cơ quan quản lý coi cookie đo lường là không thiết yếu thì thiếu đồng ý.
- *Khắc phục:* nhóm quyết định (a) giữ, ghi lập luận vào Bảng đăng ký; hoặc (b) thêm hộp đồng ý, chỉ đặt `n25_*` sau khi khách đồng ý. Nếu sau này dùng điểm ảnh của nền tảng quảng cáo (bài 6) thì **bắt buộc** phương án (b).

**P4. Lịch sử git công khai chứa bản dump CSDL với đơn mẫu.**

- *Hiện trạng:* `db-init.sql` có đơn và khách mẫu; khoá đã xoá ở bản hiện tại.
- *Yêu cầu:* Y8 (chỉ thu và lưu dữ liệu cần thiết).
- *Rủi ro:* thấp vì là dữ liệu mẫu. Nếu có dữ liệu thật thì là lộ dữ liệu cá nhân.
- *Khắc phục:* quy tắc "không bao giờ commit bản dump có dữ liệu thật"; tạo lại `db-init.sql` chỉ chứa dữ liệu mẫu tối thiểu.

## Bước 4 — Ma trận khắc phục

| Phát hiện | Mức độ | Công sức | Thời hạn | Người phụ trách | Trạng thái |
|---|---|---|---|---|---|
| P1 Xuất xứ, thương hiệu | Cao | Trung bình (40 SP) | Trước TH15 | Nhóm nội dung | Chưa làm — cần dữ liệu nhà cung cấp |
| P2 Thông tin người bán thật | Cao (khi bán thật) | Thấp | Trước khi mở bán | Trưởng nhóm | Ghi nhãn "dữ liệu mẫu" |
| P3 Đồng ý cookie | Trung bình | Thấp | Trước khi chạy quảng cáo | Nhóm kỹ thuật | Chờ nhóm quyết định |
| P4 Bản dump trong repo | Thấp | Thấp | Trước TH15 | Nhóm kỹ thuật | Khoá đã xoá; còn dữ liệu đơn mẫu |
| Đã xử lý: quảng cáo không chứng minh được, phí ship không khớp đề, khoá trong mã | — | — | 11/10 | — | Xong (commit 8697652, b47bd0d) |
