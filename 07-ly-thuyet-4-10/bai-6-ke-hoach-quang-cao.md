# Bài thực hành 6 — Kế hoạch quảng cáo có kiểm chứng tài chính (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Bảng tính có công thức liên kết: `bang-tinh-ly-thuyet-4-10.xlsx` (sheet `GiaDinh`, `SanPham`, `LT6_NguongChi`, `LT6_3TinhHuong`). Đổi ô vàng thì mọi kết quả đổi theo. Số liệu đầu vào là **dữ liệu mẫu** của học phần (xem báo cáo buổi 13); CPC tham khảo lấy từ `ad_spend.csv` mẫu, **nhóm cần tra lại** bằng công cụ dự báo từ khoá và ghi ngày tra.

## Bước 1 — Ngưỡng chi tối đa

**Sản phẩm chủ lực:** Ba lô có phản quang (BL-BL-029), giá bán 350.000đ, giá vốn 260.000đ. Chọn sản phẩm này vì có kiểm định an toàn, đúng với định vị "an toàn hơn giá" ở buổi 5, và thuộc nhóm hàng có doanh thu lớn thứ hai trong dữ liệu mẫu.

**Chi phí tính vào giá vốn:** giá nhập; đóng gói 5.000đ/đơn; hoàn hàng 3% × (2 lần phí ship + đóng gói) = 2.046đ/đơn. Phí vận chuyển **không** tính vào giá vốn vì đơn trung bình 434.252đ thấp hơn ngưỡng miễn phí 499.000đ, nên khách tự trả ship.

| Chỉ số | Giá trị | Công thức |
|---|---:|---|
| Biên gộp sản phẩm | 25,71% | (350.000 − 260.000) ÷ 350.000 |
| Chi phí logistics một đơn | 7.046đ | 5.000 + 3% × 68.200 |
| **Biên gộp thực** | **24,09%** | 25,71% − 7.046 ÷ 434.252 |
| **ROAS hoà vốn — sản phẩm chủ lực** | **4,15** | 1 ÷ 24,09% |
| ROAS hoà vốn — bình quân cửa hàng | 2,92 | 1 ÷ 34,23% |
| CPA tối đa theo đơn | 104.619đ | 434.252 × 24,09% |
| CPA tối đa theo LTV (CAC tối đa) | 330.776đ | 992.329 ÷ 3 |

| Kênh | CPC tối đa theo đơn | CPC tối đa theo LTV | CPC tham khảo | Kết luận |
|---|---:|---:|---:|---|
| Tìm kiếm trả phí (CVR 3,07%) | **3.212đ** | 10.155đ | 3.186đ | Vừa đủ theo đơn: CPC tham khảo chỉ thấp hơn ngưỡng 0,8%, không còn dư địa |
| Quảng cáo MXH (CVR 1,42%) | **1.486đ** | 4.697đ | 1.396đ | Khả thi theo đơn, dư địa 6% |

**Kết luận bước 1:** theo đơn đầu tiên, cả hai kênh chỉ vừa đủ hoà vốn. Mức chi cao hơn (đến 10.155đ) chỉ hợp lý nếu chấp nhận lỗ ở đơn đầu và thu lại nhờ khách mua lại. Đó là giả định 1,5 năm gắn bó, **chưa kiểm chứng** (lý thuyết buổi 6, slide 11). Nhóm chọn **ngưỡng theo đơn** để đặt giá thầu, và dùng ngưỡng LTV làm giới hạn trên tuyệt đối.

> Báo cáo buổi 13 (mục 3.3) từng kết luận "MXH dưới ngưỡng, có thể tăng ngân sách" vì chỉ dùng ngưỡng LTV. Theo ngưỡng đơn của bài này thì dư địa chỉ 6%, nên điều chỉnh thành: **chỉ tăng ngân sách MXH sau khi sửa điểm rơi thanh toán** (phương án B+C buổi 13).

## Bước 2 — Cấu trúc chiến dịch

Chiến dịch dẫn thẳng vào trang danh mục hoặc trang sản phẩm khớp truy vấn, không dẫn về trang chủ (buổi 13, điểm rơi Phiên → Xem SP của MXH).

| Chiến dịch | Nhóm quảng cáo (cùng ý định, cùng loại SP) | Từ khoá / đối tượng | Đối sánh | Trang đích | Giai đoạn phễu |
|---|---|---|---|---|---|
| TK-Ba lô | Ba lô chống gù (giao dịch) | "mua ba lô chống gù lớp 1", "ba lô chống gù cho bé" | Cụm từ → chính xác | `/product-category/ba-lo-tui/ba-lo/` | Quyết định |
| TK-Ba lô | Ba lô an toàn (so sánh) | "ba lô chống gù loại nào tốt", "ba lô có phản quang" | Cụm từ | Trang Ba lô có phản quang | Cân nhắc |
| TK-Theo lớp | Bộ đồ dùng lớp 1 | "bộ đồ dùng học tập lớp 1 đầy đủ", "vở ô ly lớp 1" | Cụm từ | Danh mục lọc sẵn khối lớp 1 | Quyết định |
| MXH-Tiếp thị lại | Tầng 1: đã bắt đầu thanh toán, chưa mua (7 ngày) | Sự kiện `begin_checkout` không có `purchase` | — | Giỏ hàng | Quyết định |
| MXH-Tiếp thị lại | Tầng 2: đã thêm giỏ, chưa thanh toán (14 ngày) | `add_to_cart` không có `begin_checkout` | — | Sản phẩm đã thêm giỏ | Cân nhắc |
| MXH-Tiếp thị lại | Tầng 3: chỉ xem danh mục/sản phẩm (30 ngày) | `view_item` không có `add_to_cart` | — | Bài so sánh ba lô (bản đồ nội dung buổi 5) | Tìm hiểu |
| MXH-Nhận biết | Phụ huynh có con tiểu học, mùa tựu trường | Sở thích: giáo dục tiểu học; tuổi 28–45 | — | Danh mục theo khối lớp | Nhận biết |

Tầng tiếp thị lại dựng từ bảng sự kiện `wp_nhom25_events` (buổi 13). Khi tải tệp khách lên nền tảng quảng cáo phải có đồng ý (slide 5); hiện cửa hàng **chưa** xin đồng ý này, nên tiếp thị lại chỉ dùng điểm ảnh của nền tảng **sau khi khách đồng ý cookie quảng cáo**.

**Từ khoá phủ định (≥ 10):**

| Từ khoá phủ định | Lý do loại |
|---|---|
| miễn phí, cho không | Tìm hàng không mất tiền |
| cũ, thanh lý, 2hand | Tìm hàng đã qua sử dụng |
| tự làm, cách làm, DIY | Tìm hướng dẫn tự làm |
| tuyển dụng, việc làm | Tìm việc |
| sỉ, giá sỉ, bỏ sỉ | Cửa hàng chỉ bán lẻ |
| hình ảnh, tô màu, vẽ | Tìm tranh ảnh, không mua hàng |
| pdf, tải về | Tìm tài liệu số |
| sửa, thay khoá | Tìm sửa chữa |
| Tên các sàn/cửa hàng đối thủ | Ý định điều hướng tới đối thủ |
| balo laptop, balo du lịch | Sai đối tượng (không phải học sinh) |

## Bước 3 — Dự báo ba tình huống (ngân sách thử 10 triệu/kênh/tháng)

| Kênh | Tình huống | CPC | CVR | Đơn | ROAS | Trong ngưỡng 2,92? |
|---|---|---:|---:|---:|---:|---|
| Tìm kiếm | Bất lợi | 4.142đ | 2,46% | 59 | **2,58** | **Không** |
| Tìm kiếm | Cơ sở | 3.186đ | 3,07% | 96 | 4,18 | Có |
| Tìm kiếm | Thuận lợi | 2.230đ | 3,68% | 165 | 7,17 | Có |
| MXH | Bất lợi | 1.815đ | 1,14% | 63 | **2,72** | **Không** |
| MXH | Cơ sở | 1.396đ | 1,42% | 102 | 4,42 | Có |
| MXH | Thuận lợi | 977đ | 1,70% | 174 | 7,57 | Có |

**Kết luận từ tình huống bất lợi:** cả hai kênh đều **dưới ngưỡng hoà vốn** khi CPC cao hơn 30% và CVR giảm 20%. Vì vậy:

1. Bắt đầu với **ngân sách thử nhỏ** (5 triệu/kênh) trong 4 tuần, không phải 10 triệu.
2. Chỉ chạy nhóm **cụm từ dài, cạnh tranh thấp** ở bảng trên; chưa chạy từ khoá ngắn như "ba lô".
3. **Sửa trang thanh toán trước** (phương án B+C buổi 13): CVR tăng thì cả ba tình huống dịch lên. Đây là cách giảm CPA bền nhất (slide 7, 10).

## Bước 4 — Hệ chỉ số ba tầng và quy kết

| Tầng | Chỉ số | Cách đo | Ngưỡng mục tiêu |
|---|---|---|---|
| Kết quả kinh doanh | Lợi nhuận gộp sau quảng cáo | Doanh thu đơn có nguồn quảng cáo × 34,23% − chi phí quảng cáo | > 0 mỗi tháng |
| Kết quả kinh doanh | LTV theo kênh thu hút | Như buổi 13, tách theo `src` của đơn đầu | ≥ 3 × CAC |
| Hiệu quả | ROAS theo **đơn website** (không theo nền tảng báo) | Doanh thu từ `purchase` có `src` = kênh ÷ chi phí | ≥ 2,92 (bình quân), ≥ 4,15 (ba lô) |
| Hiệu quả | CPA | Chi phí ÷ số `purchase` của kênh | ≤ 104.619đ |
| Hiệu quả | Tỷ lệ chuyển đổi phiên → đơn | `purchase` ÷ `session` theo nguồn | ≥ 3,07% (TK), ≥ 1,42% (MXH) |
| Hoạt động | CPC | Chi phí ÷ lượt nhấp (nền tảng) | ≤ CPC tối đa bước 1 |
| Hoạt động | Phiên / lượt nhấp | `session` có UTM ÷ lượt nhấp nền tảng | ≥ 80% (buổi 13 đo được 18% → sai gắn thẻ) |

Bỏ khỏi bảng: số người theo dõi, tổng lượt hiển thị (chỉ số phù phiếm, không nối được lên tầng trên).

**Mô hình quy kết:** dùng **suy giảm theo thời gian**, vì kế hoạch có 3 kênh (tìm kiếm, MXH nhận biết, MXH tiếp thị lại) và chu kỳ quyết định của phụ huynh kéo dài vài ngày tới vài tuần trước tựu trường.

- **Giới hạn:** mô hình này đánh giá thấp chiến dịch **MXH-Nhận biết**, vốn đứng đầu hành trình.
- **Cách bù:** chạy **thử nghiệm tăng thêm**, tạm dừng MXH-Nhận biết 2 tuần ở một nửa tỉnh (ví dụ miền Nam) rồi so số đơn với nửa còn lại.
- Plugin đo lường hiện ghi nguồn theo **phiên cuối**, tức tương tác cuối. Muốn quy kết suy giảm thời gian cần lưu chuỗi phiên theo khách; đây là việc ghi vào hạn chế.

**Ngưỡng dừng đặt trước:**

- CPA của một nhóm quảng cáo vượt 104.619đ trong **7 ngày liên tục** → dừng nhóm đó.
- ROAS theo đơn website của kênh dưới 2,0 trong **14 ngày** → dừng kênh.
- Phiên / lượt nhấp dưới 50% → dừng và sửa gắn thẻ UTM trước.

## Điểm cộng — kịch bản tự động hoá nhắc giỏ hàng

```
[add_to_cart] ──(không có begin_checkout/purchase trong 1 giờ)──► Có email và đã đồng ý nhận thư? ──Không──► Dừng
                                                                       │Có
                                                                       ▼
                                               +1 giờ: Thư 1 — chỉ nhắc, link quay lại giỏ
                                                                       │ (đã purchase? → dừng)
                                                                       ▼
                                               +24 giờ: Thư 2 — đánh giá người đã mua, chính sách đổi trả 7 ngày
                                                                       │ (đã purchase hoặc hủy đăng ký? → dừng)
                                                                       ▼
                                               +72 giờ: Thư 3 — ưu đãi miễn phí ship cho giỏ này, hạn 48 giờ
```

**Điều kiện tuân thủ:**

- Chỉ gửi cho người đã **chủ động** tick ô đồng ý nhận thư. Ô để trống mặc định, tách khỏi ô đồng ý điều khoản (buổi 8).
- Mỗi thư có link hủy đăng ký hoạt động ngay.
- Ưu đãi chỉ ở thư 3 để khách không học thói quen bỏ giỏ chờ giảm giá (slide 20).
- **Hiện trạng:** cửa hàng chưa có ô đồng ý nhận thư và chưa thu email trước khi đặt hàng, nên kịch bản này **chưa chạy được**. Muốn chạy phải thêm ô đồng ý và cập nhật Chính sách quyền riêng tư cùng Bảng đăng ký dữ liệu.
