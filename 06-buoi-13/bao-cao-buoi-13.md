# Buổi 13 — Marketing, đo lường và quyết định theo số liệu (Nhóm 25 · Bút Chì Xanh)

**Dữ liệu:** `02-dataset/traffic_daily.csv` (60 ngày, 01/06–30/07/2026, 5 nguồn), `ad_spend.csv` (4 chiến dịch, cùng kỳ), `customers_orders.csv` (300 khách, 12 tháng), `products.csv` (40 sản phẩm, giá bán và giá vốn).
**Tính toán:** mọi con số trong báo cáo được tạo bởi `phan-tich.py` và nằm trong `phan-tich-buoi-13.xlsx`. Chạy lại: `python 06-buoi-13/phan-tich.py`.

---

## NV1 · Thiết lập đo lường — *chưa triển khai trên cửa hàng*

Phần này cần nhóm chọn công cụ đo (ví dụ GA4) và tạo tài khoản. Dưới đây là thiết kế để triển khai:

| Sự kiện | Tên sự kiện (GA4) | Gắn vào phần tử nào | Ghi chú |
|---|---|---|---|
| Phiên truy cập | `session_start` | tự động | |
| Xem trang sản phẩm | `view_item` | mọi trang `/product/...` | phải khai báo riêng |
| Thêm vào giỏ | `add_to_cart` | nút "Thêm vào giỏ" (cả trang sản phẩm và danh sách) | phải khai báo riêng |
| Đơn hoàn tất | `purchase` (kèm `transaction_id` = mã đơn) | trang "Đã nhận đơn" | phải khai báo riêng; dùng mã đơn để không đếm trùng khi khách F5 |

- **Thẻ nguồn:** mọi liên kết chia sẻ ra ngoài đều gắn `utm_source`, `utm_medium`, `utm_campaign`. Ví dụ bài Facebook khai trương: `https://butchixanh.local/?utm_source=facebook&utm_medium=social&utm_campaign=khaitruong`.
- **Phân loại mã:**
  - Mã phục vụ quảng cáo (Facebook Pixel, Google Ads) **chỉ nạp sau khi khách bấm đồng ý** trên banner cookie.
  - Mã thống kê vận hành nội bộ thuộc nhóm "thống kê", có lựa chọn từ chối riêng.
- **Kiểm chứng:** đặt 1 đơn thử, chụp DebugView thấy đủ 4 sự kiện.
- ⚠️ Khi triển khai phải **sửa Chính sách quyền riêng tư và Bảng đăng ký dữ liệu của buổi 12**. Hai văn bản này hiện ghi "không dùng Google Analytics / Facebook Pixel".

**Vì sao NV1 quan trọng với chính bộ dữ liệu này** (xem mục 2.4): lượt nhấp nền tảng quảng cáo báo gấp **5,5 lần** số phiên website ghi nhận cho tìm kiếm trả phí. Phần lớn lưu lượng quảng cáo đang không được gắn đúng nguồn, nên phải sửa đo lường trước khi tin vào các con số theo nguồn.

---

## NV2 · Dựng phễu và chẩn đoán điểm rơi

### 2.1 Bảng phễu — toàn bộ và từng nguồn (60 ngày)

| Nguồn | Phiên | Xem SP | Thêm giỏ | Thanh toán | Hoàn tất | Doanh thu |
|---|---:|---:|---:|---:|---:|---:|
| organic | 16.369 | 7.168 | 2.130 | 1.272 | 618 | 267.697.000 |
| paid_search | 13.512 | 6.037 | 1.809 | 1.088 | 415 | 187.261.000 |
| social_paid | 19.693 | 5.570 | 1.707 | 1.012 | 279 | 127.702.000 |
| social_organic | 6.866 | 3.010 | 916 | 547 | 74 | 34.165.000 |
| email | 5.344 | 3.356 | 1.012 | 616 | 274 | 123.714.000 |
| **Tổng** | **61.784** | **25.141** | **7.574** | **4.535** | **1.660** | **740.539.000** |

### 2.2 Tỷ lệ chuyển tiếp từng bước (dùng để chẩn đoán)

| Nguồn | Phiên→Xem SP | Xem SP→Giỏ | Giỏ→Thanh toán | Thanh toán→Hoàn tất | *Toàn phễu (chỉ để so sánh)* |
|---|---:|---:|---:|---:|---:|
| organic | 43,8% | 29,7% | 59,7% | **48,6%** | 3,78% |
| paid_search | 44,7% | 30,0% | 60,1% | 38,1% | 3,07% |
| social_paid | **28,3%** ⚠️ | 30,6% | 59,3% | **27,6%** ⚠️ | 1,42% |
| social_organic | 43,8% | 30,4% | 59,7% | **13,5%** ⚠️ | 1,08% |
| email | 62,8% | 30,2% | 60,9% | 44,5% | 5,13% |
| Tổng | 40,7% | 30,1% | 59,9% | 36,6% | 2,69% |

**Đọc bảng:**
1. **Hai bước giữa phễu như nhau ở mọi nguồn.** Xem SP→Giỏ khoảng 30% và Giỏ→Thanh toán khoảng 60%, chênh nhau tối đa 1,5 điểm %, nằm trong khoảng tin cậy của nhau. Đây là đặc tính chung của website, không phải lỗi của nguồn nào.
2. **Điểm rơi riêng của từng nguồn nằm ở hai đầu phễu:**
   - **social_paid ở bước đầu:** chỉ 28,3% phiên mở trang sản phẩm, trong khi 3 nguồn còn lại (trừ email) đạt khoảng 44%.
   - **Hai nguồn mạng xã hội ở bước cuối:** Thanh toán→Hoàn tất của social_organic là 13,5% và social_paid là 27,6%, so với organic 48,6%.
3. **Nếu chỉ nhìn phễu gộp**, bước cuối 36,6% trông như "trang thanh toán của website có vấn đề". Thực tế organic và email hoàn tất bình thường; điểm rơi do 2 nguồn MXH kéo xuống. Đây đúng là sai lầm "gộp chung → kết luận sai" mà đề bài cảnh báo.

### 2.3 Ngưỡng dữ liệu — mức tin cậy của các kết luận

- **Cả 60 ngày:** mỗi bước của mỗi nguồn có ít nhất 547 người vào bước, nên khoảng tin cậy 95% hẹp. Ví dụ Thanh toán→Hoàn tất của social_organic là 13,5% [10,7% – 16,4%], của organic là 48,6% [45,8% – 51,3%]. Hai khoảng **không chồng nhau**, nên chênh lệch là thật, không phải do ngẫu nhiên.
- **Ổn định theo tháng:** tháng 6 / tháng 7 lần lượt là 13,6% / 13,5% (social_organic), 27,4% / 27,7% (social_paid), 47,7% / 49,3% (organic).
- **Theo ngày thì không kết luận được:** mỗi nguồn chỉ có 57–486 phiên/ngày. Tỷ lệ Thanh toán→Hoàn tất theo ngày của organic dao động từ 36% đến 67%, của social_organic từ 8% đến 20%. Không rút kết luận từ số liệu một ngày hay một tuần của một nguồn.
- **Giới hạn của cả bộ dữ liệu:** xem 2.4. Phân chia nguồn có thể sai do gắn thẻ thiếu, nên các kết luận theo nguồn cần kiểm lại sau khi làm NV1.

### 2.4 Đối chiếu số liệu nền tảng quảng cáo với website

| Kênh | Lượt nhấp (nền tảng) | Phiên (website) | Phiên / lượt nhấp | Chuyển đổi nền tảng báo | Đơn website ghi nhận | Nền tảng / website | ROAS nền tảng báo | ROAS theo đơn website |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| paid_search | 73.889 | 13.512 | 18% | 3.778 | 415 | 9,1 lần | 7,5 | **0,80** |
| social_paid | 40.008 | 19.693 | 49% | 1.409 | 279 | 5,1 lần | 12,1 | **2,29** |

**Kết luận:**
- Nền tảng quảng cáo **tự quy kết** chuyển đổi rất rộng: tính cả người chỉ nhìn thấy quảng cáo rồi tự vào sau, và cả khách cũ.
- Phiên từ quảng cáo bị thiếu thẻ nguồn, nên chỉ 18% lượt nhấp tìm kiếm trả phí được website ghi đúng nguồn. Phần còn lại nhiều khả năng bị đếm vào organic, làm organic trông tốt hơn thực tế.
- Bảng tổng hợp buổi trước tính CAC và ROAS theo số của nền tảng (CAC tìm kiếm 62.306đ, ROAS 10,0) là **quá lạc quan**. Báo cáo này dùng **số đo trên website** cho doanh thu và đơn, dùng số của nền tảng chỉ cho chi phí và lượt nhấp.

### 2.5 Chọn điểm can thiệp — bằng phép tính

**Công thức:** đơn tăng thêm ≈ số người vào bước × mức cải thiện tỷ lệ × tích các tỷ lệ phía sau.

**Bước 1 — Độ nhạy:** số đơn tăng thêm trong 60 ngày cho mỗi +1 điểm % ở từng bước. Phép tính này không cần giả định mức cải thiện.

| Bước | Toàn site | social_paid | social_organic | Hai nguồn MXH cộng lại |
|---|---:|---:|---:|---:|
| Phiên→Xem SP | 61.784 × 1% × 6,60% = **41** | 19.693 × 1% × 5,01% = 10 | 2 | 12 |
| Xem SP→Giỏ | 25.141 × 1% × 21,92% = **55** | 9 | 2 | 11 |
| Giỏ→Thanh toán | 7.574 × 1% × 36,60% = **28** | 5 | 1 | 6 |
| Thanh toán→Hoàn tất | 4.535 × 1% × 100% = **45** | 1.012 × 1% = 10 | 547 × 1% = 5 | 15 |

Tính theo từng điểm %, bước Xem SP→Giỏ của toàn site có đòn bẩy lớn nhất. Nhưng chọn điểm can thiệp còn phụ thuộc **có thể cải thiện bao nhiêu điểm %**, nên cần so các phương án cụ thể.

**Bước 2 — So sánh phương án.** Mức cải thiện lấy theo bằng chứng: thu hẹp **một nửa** khoảng cách tới nguồn đang làm tốt hơn trên cùng website.

| Phương án | Phép tính (60 ngày) | Đơn tăng thêm / 60 ngày | / tháng | Căn cứ mức cải thiện |
|---|---|---:|---:|---|
| A. Trang đích quảng cáo MXH (social_paid, Phiên→Xem SP 28,3% → 36,2%) | 19.693 × 7,91% × (30,65% × 59,29% × 27,57%) | 78 | 39 | Một nửa khoảng cách tới 44,1% (trung bình organic, paid_search, social_organic) |
| B. Hoàn tất thanh toán social_paid (27,6% → 38,1%) | 1.012 × 10,51% × 1 | 106 | 53 | Một nửa khoảng cách tới organic 48,6% |
| C. Hoàn tất thanh toán social_organic (13,5% → 31,1%) | 547 × 17,53% × 1 | 96 | 48 | Một nửa khoảng cách tới organic 48,6% |
| **B + C (cùng một cơ chế, một lần sửa)** | 106 + 96 | **202** | **101** | |
| D. Xem SP→Giỏ toàn site (30,1% → 33,1%) | 25.141 × 3% × (59,88% × 36,60%) | 165 | 83 | **Giả định +3 điểm %** — không có nguồn nào làm tốt hơn để làm căn cứ |
| E. Giỏ→Thanh toán toàn site (59,9% → 62,9%) | 7.574 × 3% × 36,60% | 83 | 42 | **Giả định +3 điểm %** |

**Quyết định: chọn B + C**, sửa bước hoàn tất thanh toán cho lưu lượng mạng xã hội.
- **Nhiều đơn nhất:** **+202 đơn / 60 ngày** (≈ +101 đơn/tháng, **+12,2%** tổng số đơn). Tính theo đơn trung bình 446.108đ thì doanh thu thêm ≈ 90 triệu/60 ngày; nhân tỷ lệ gộp 34,2% thì lợi nhuận gộp thêm ≈ **30,8 triệu / 60 ngày**.
- **Bằng chứng mạnh nhất:** khoảng tin cậy tách biệt và ổn định qua 2 tháng (mục 2.3). Có sẵn "mức chuẩn" ngay trên cùng website là organic, nên biết chắc có thể làm tốt hơn.
- **So với D:** muốn D vượt B + C, bước Xem SP→Giỏ phải tăng ít nhất 202 / 55 ≈ **+3,7 điểm %** (30,1% → 33,8%). Hiện không có bằng chứng nào cho thấy mức đó đạt được, vì mọi nguồn đều đúng 30%. D để làm sau, khi có thử nghiệm.
- **A làm tiếp theo:** A nhỏ hơn (+39 đơn/tháng) nhưng gắn với tiền quảng cáo đang chi. Sửa A làm giảm chi phí mỗi đơn của social_paid.

### 2.6 Nối nguyên nhân với biện pháp

| Điểm rơi | Cơ chế khả dĩ (suy ra từ dấu hiệu) | Biện pháp khớp cơ chế | Cần kiểm chứng trước bằng |
|---|---|---|---|
| **MXH: Thanh toán→Hoàn tất thấp** | Khách MXH đã qua đủ các bước trước như mọi nguồn (Giỏ→Thanh toán vẫn 59–60%), nên **không phải do thiếu ý định mua** mà do **kỹ thuật ở bước trả tiền**. Khách MXH chủ yếu mở site trong **trình duyệt nhúng của Facebook/Zalo**. Khi chuyển sang VNPay, mở app ngân hàng lấy OTP rồi quay lại, trình duyệt nhúng hay mất phiên hoặc cookie, nên đơn kẹt ở "chờ thanh toán". Phí ship không phải nguyên nhân: phí hiện từ trang sản phẩm (buổi 12) và áp như nhau cho mọi nguồn. | Nhận diện trình duyệt nhúng ở trang thanh toán: **đặt COD làm lựa chọn mặc định** và hiện nút "Mở bằng trình duyệt để trả qua VNPay"; giữ giỏ và đơn khi quay lại từ VNPay. | Tách tỷ lệ hoàn tất theo trình duyệt/thiết bị và theo phương thức thanh toán (cần NV1); xem tỷ lệ đơn VNPay "chờ thanh toán" của khách MXH |
| **social_paid: Phiên→Xem SP thấp** | Đặc trưng của chiến dịch **MXH-nhan-biet**: nền tảng báo 0,6% chuyển đổi trên lượt nhấp, so với 4,7% của MXH-tiep-thi-lai. Người lướt thấy quảng cáo nhận biết, bấm vào trang chủ, chưa có sản phẩm cụ thể để xem nên thoát. | Quảng cáo dẫn **thẳng vào trang sản phẩm/danh mục** đúng với hình trong quảng cáo; gắn `utm_campaign` riêng cho từng quảng cáo | Tách Phiên→Xem SP theo `utm_campaign` (cần NV1) |
| (Không chọn) Xem SP→Giỏ 30% đều | Không có dấu hiệu lỗi riêng; có thể là mức bình thường của ngành | Chỉ thay đổi khi có thử nghiệm A/B | — |

---

## NV3 · Giá trị vòng đời và ngưỡng chi thu hút

### 3.1 Tỷ lệ lợi nhuận gộp — bình quân có trọng số theo doanh thu

| Nhóm hàng | Tỷ lệ gộp của nhóm* | Doanh thu 12 tháng | Tỷ trọng | Đóng góp |
|---|---:|---:|---:|---:|
| Bút viết | 34,31% | 128.605.000 | 22,18% | 7,61% |
| Vở & sổ | 38,60% | 97.126.000 | 16,75% | 6,47% |
| Dụng cụ học tập | 34,68% | 107.680.000 | 18,57% | 6,44% |
| Ba lô & túi | 30,85% | 121.535.000 | 20,96% | 6,47% |
| Mỹ thuật | 33,68% | 124.781.000 | 21,52% | 7,25% |
| **Bình quân có trọng số** | | **579.727.000** | 100% | **34,23%** |
| *(Sai)* Bình quân đơn giản 40 sản phẩm | 34,56% | | | |

\* Tỷ lệ gộp nhóm = (Σ giá bán − Σ giá vốn) / Σ giá bán của các sản phẩm trong nhóm. Dữ liệu không có số lượng bán từng sản phẩm, nên trọng số doanh thu được lấy theo **nhóm hàng chính** của 300 khách.

Bảng tổng hợp buổi trước ghi 34,55% "theo doanh thu", nhưng con số này thực chất là **bình quân đơn giản** (34,56%). Lỗi nhỏ ở bộ dữ liệu này vì các nhóm có biên gần nhau, nhưng là đúng lỗi đề bài cảnh báo: Ba lô & túi có biên thấp nhất mà chiếm 21% doanh thu.

### 3.2 Giá trị vòng đời (LTV)

| Thành phần | Giá trị | Nguồn |
|---|---:|---|
| Đơn trung bình | 434.252đ | 579.727.000đ / 1.335 đơn |
| Số đơn / năm | 4,45 | 1.335 đơn / 300 khách trong 12 tháng |
| Số năm gắn bó | **1,5 — GIẢ ĐỊNH** | xem căn cứ dưới |
| Tỷ lệ lợi nhuận gộp | 34,23% | mục 3.1 |
| **LTV** | 434.252 × 4,45 × 1,5 × 34,23% = **992.329đ** | |

**Căn cứ giả định 1,5 năm.** Website chưa có khách nào được quan sát quá 12 tháng (khách đầu tiên từ 06/08/2025).
- 76% khách đã mua hơn 1 đơn trong năm đầu, và số đơn không giảm theo thâm niên (khách 2–4 tháng: 4,1 đơn; khách 10–12 tháng: 4,7 đơn), nên khách đang gắn bó.
- Đồ dùng học tập theo năm học: phụ huynh mua cho con qua nhiều năm, nhưng đổi cửa hàng dễ, không có chi phí chuyển đổi.
- Lấy 1,5 năm tương đương giả định mỗi năm mất khoảng 2/3 số khách (thời gian gắn bó trung bình = 1 / tỷ lệ rời bỏ = 1 / 0,67). Đây là mức thận trọng so với tỷ lệ mua lại 76%.
- **Cách kiểm chứng:** sau tháng 08/2026, đo tỷ lệ khách năm đầu còn mua trong năm thứ hai, rồi thay số.

**Độ nhạy theo giả định:**

| Số năm gắn bó | LTV | Ngưỡng chi thu hút (LTV / 3) |
|---:|---:|---:|
| 1,0 | 661.552đ | 220.517đ |
| **1,5** | **992.329đ** | **330.776đ** |
| 2,0 | 1.323.105đ | 441.035đ |
| 3,0 | 1.984.657đ | 661.552đ |

### 3.3 Ngưỡng chi thu hút và mức chi mỗi lượt nhấp

- **Ngưỡng chi thu hút một khách:** LTV / 3 = **330.776đ**, tức tỷ lệ LTV/CAC ≥ 3.
- **Mức chi tối đa mỗi lượt nhấp** = ngưỡng chi × tỷ lệ chuyển đổi đã đo (đơn / phiên trên website, mục 2.2):

| Kênh | Phép tính | CPC tối đa | CPC nền tảng báo | Chi phí trên mỗi phiên website đo được | Chi phí mỗi đơn website đo được | LTV / chi phí mỗi đơn |
|---|---|---:|---:|---:|---:|---:|
| Tìm kiếm trả phí | 330.776 × 3,07% | **10.159đ** | 3.186đ | **17.421đ** ❌ | **567.210đ** ❌ | 1,75 |
| Quảng cáo MXH | 330.776 × 1,42% | **4.686đ** | 1.396đ | 2.836đ ✅ | 200.194đ ✅ | 4,96 |

**Quyết định:**
- **Quảng cáo MXH:** đang dưới ngưỡng, có thể tăng ngân sách. Đặt giá thầu tối đa **4.686đ/lượt nhấp**. Nếu sửa xong điểm rơi B (tỷ lệ chuyển đổi tăng), ngưỡng CPC cũng tăng theo.
- **Tìm kiếm trả phí:** CPC nền tảng báo (3.186đ) thấp hơn trần, nhưng chỉ 18% lượt nhấp trở thành phiên có gắn nguồn. Tính trên số đo được thì mỗi đơn tốn 567.210đ, gấp 1,7 lần ngưỡng.
  - **Không tăng ngân sách** cho tới khi gắn thẻ nguồn đúng (NV1) để biết con số thật.
  - Riêng chiến dịch **TK-thuong-hieu** (149,6 triệu, 64% chi tìm kiếm) chủ yếu bắt người vốn đã tìm tên cửa hàng: nên thử **giảm 50% ngân sách 2 tuần** và đo xem tổng đơn có giảm không.
- **Lưu ý:** "chi phí mỗi đơn" ở đây tính trên mọi đơn, kể cả khách cũ quay lại. Chi phí cho một **khách mới** còn cao hơn, nên kết luận về tìm kiếm trả phí đã là mức nhẹ nhất.

**LTV theo kênh thu hút** (cùng giả định 1,5 năm): organic 1.019.874đ, paid_search 1.043.249đ, social_paid 932.804đ, social_organic 1.093.967đ, email 870.693đ. Mỗi kênh chỉ có 54–67 khách, nên chênh lệch giữa kênh **chưa đủ tin cậy**; dùng LTV chung cho mọi kênh.

### 3.4 Ba thư giữ chân

Gửi cho **139/300 khách (46%) đã đồng ý nhận thư** (`da_dang_ky_thu = Co`). Chi tiết điều kiện, nội dung, chỉ số: [ba-thu-giu-chan.md](ba-thu-giu-chan.md).

| Thư | Điều kiện kích hoạt (thời gian + hành vi) | Chỉ số đánh giá chính |
|---|---|---|
| 1. Chào mừng và gợi ý đơn thứ hai | 5 ngày sau khi đơn **đầu tiên** giao thành công, **và** chưa có đơn thứ hai | Tỷ lệ đặt đơn thứ hai trong 30 ngày (so với nhóm đối chứng 10% không nhận thư) |
| 2. Nhắc mua lại theo chu kỳ | 70 ngày sau đơn gần nhất (chu kỳ trung bình 365 / 4,45 ≈ 82 ngày), **và** chưa mua lại, **và** đơn trước có hàng tiêu hao (bút, vở, màu) | Tỷ lệ mua lại trong 14 ngày; doanh thu trên mỗi thư gửi |
| 3. Gọi khách quay lại | 150 ngày không mua (≈ 2 chu kỳ), **và** không mở thư nào trong 60 ngày; gửi sớm hơn nếu rơi vào đợt tựu trường tháng 8 | Tỷ lệ khách quay lại mua trong 30 ngày; tỷ lệ hủy đăng ký |

**Ràng buộc:**
- **Cơ sở gửi thư:** sự đồng ý của khách theo Luật Bảo vệ dữ liệu cá nhân và quy định chống thư rác. Ô "Đồng ý nhận ưu đãi qua email" **không đánh dấu sẵn**; thư xác nhận đơn hàng không được ghép nội dung quảng cáo.
- **Hủy đăng ký:** mỗi thư có liên kết "Hủy nhận thư" một bấm; ngừng gửi ngay khi khách bấm, chậm nhất trong 24 giờ.
- ⚠️ Website hiện **chưa có ô đồng ý nhận thư**, và Chính sách quyền riêng tư của buổi 12 ghi "không có biểu mẫu đăng ký nhận tin". Trước khi gửi phải thêm ô đồng ý và cập nhật 2 văn bản của buổi 12.

---

## Điểm cộng · Thiết kế thử nghiệm A/B cho điểm can thiệp đã chọn (B + C)

| Thành phần | Thiết kế |
|---|---|
| **Giả thuyết** | Với khách đến từ mạng xã hội, chọn sẵn COD và hiện nút "Mở bằng trình duyệt để trả qua VNPay" khi phát hiện trình duyệt nhúng sẽ **tăng tỷ lệ Thanh toán→Hoàn tất**, vì bỏ bớt bước chuyển app dễ làm mất phiên. |
| **Đối tượng và chia nhóm** | Mọi phiên có `utm_source` thuộc nhóm mạng xã hội và vào trang thanh toán. Chia ngẫu nhiên 50/50 **theo khách** (cookie), không theo lượt xem, để một khách luôn thấy cùng một phiên bản. |
| **Chỉ số chính (chốt trước)** | Tỷ lệ lượt bắt đầu thanh toán dẫn tới đơn hoàn tất trong 24 giờ. Hiện tại 22,6% = (279 + 74) / (1.012 + 547). |
| **Chỉ số bảo vệ (chốt trước)** | Giá trị đơn trung bình (không giảm quá 5%); tỷ lệ đơn COD bị từ chối nhận (không tăng quá 2 điểm %); tỷ lệ hủy đơn. |
| **Cỡ mẫu** | Mức cải thiện tối thiểu muốn phát hiện là **+8 điểm %** (22,6% → 30,6%, bằng khoảng một nửa mục tiêu của B + C). α = 0,05 hai phía, power 80% ⇒ **479 lượt bắt đầu thanh toán mỗi nhóm**, 958 tổng. Với khoảng 26 lượt/ngày từ MXH, cần **khoảng 37 ngày**. Nếu muốn phát hiện +5 điểm % thì cần 1.181 lượt/nhóm (khoảng 91 ngày), quá dài; +10 điểm % cần 313 lượt/nhóm (khoảng 25 ngày). |
| **Điều kiện dừng** | Chạy **đủ 958 lượt và tối thiểu trọn 5 tuần** (để có đủ các ngày trong tuần); **không xem kết quả giữa chừng để dừng sớm**. Chỉ dừng sớm nếu một chỉ số bảo vệ vượt ngưỡng (ví dụ COD bị từ chối nhận tăng > 2 điểm %). Nếu đợt tựu trường (tháng 8) rơi vào giữa thử nghiệm thì vẫn giữ cả hai nhóm chạy song song, không so trước–sau. |
| **Kết luận** | Kiểm định hai tỷ lệ (z-test); áp dụng nếu p < 0,05 **và** các chỉ số bảo vệ đạt; báo cáo cả khoảng tin cậy của mức chênh. |
| **Điều kiện tiên quyết** | Hoàn thành NV1: phải ghi được `utm_source`, sự kiện bắt đầu thanh toán và `purchase` theo từng khách, nếu không thì không đo được chỉ số chính. |
