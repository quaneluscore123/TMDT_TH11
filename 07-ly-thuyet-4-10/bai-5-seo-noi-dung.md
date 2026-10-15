# Bài thực hành 5 — Kiểm tra SEO kỹ thuật và bản đồ nội dung (Nhóm 25 · Bút Chì Xanh)

Cập nhật 11/10/2026. Kiểm tra trên `https://butchixanh.local` bằng `curl` và WP-CLI. Mã cấu hình SEO nằm ở `01-cua-hang/wp-content/mu-plugins/nhom25-seo.php`. Định vị cửa hàng (buổi 5): *đồ dùng học tập cho học sinh tiểu học, ghi rõ kiểm định an toàn và khối lớp*.

## Bước 1 — Kiểm tra 7 hạng mục SEO kỹ thuật

| # | Hạng mục | Hiện trạng | Minh chứng / cách kiểm tra | Kết luận |
|---|---|---|---|---|
| 1 | HTTPS toàn site, HTTP chuyển 301 | Mọi trang 200 qua HTTPS; HTTP → 301; HSTS 1 năm | `05-buoi-12/minh-chung/14-audit-sau-khi-sua-lan2.txt` mục [1], [2] | Đạt (chứng chỉ tự ký, chỉ hợp lệ cục bộ) |
| 2 | Sơ đồ trang (sitemap) và robots.txt | `/wp-sitemap.xml` 200; robots.txt chặn `wp-admin`, `wc-logs`, thư mục tải lên của WooCommerce, có khai báo sitemap | `curl -sk https://butchixanh.local/robots.txt` | Đạt |
| 3 | Tổ hợp bộ lọc / sắp xếp không bị lập chỉ mục | URL có `filter_*`, `pa_*`, `orderby`, `min_price`, `max_price`, `price_range` và trang tìm kiếm → `noindex,follow` | `curl -sk "https://butchixanh.local/shop/?orderby=price" \| grep robots` | Đạt (đã sửa 11/10) |
| 4 | Thẻ canonical | Trang cửa hàng và danh mục có canonical trỏ về URL gốc (giữ số trang khi phân trang); trang sản phẩm do WordPress tự in | `curl -sk https://butchixanh.local/product-category/vo-so/ \| grep canonical` | Đạt (đã sửa 11/10) |
| 5 | Thẻ mô tả riêng từng trang | Trang chủ, cửa hàng, danh mục, sản phẩm, trang chính sách đều có mô tả riêng ≤ 155 ký tự | `grep 'name="description"'` trên từng loại trang | Đạt (đã sửa 11/10) |
| 6 | Hiển thị trên điện thoại | Giao diện Storefront co giãn theo màn hình; thẻ viewport có sẵn | `04-minh-chung/22…27-*.png` | Đạt. Nhóm cần chụp thêm ảnh ở độ rộng 375px |
| 7 | Tốc độ tải | Đã bật nén, lưu đệm trình duyệt, tối ưu ảnh (buổi 3) | `04-minh-chung/09…12-hieu-nang-*.png` (trước và sau) | Đạt ở mức cục bộ; cần đo lại khi có tên miền thật |

**Lỗi đã sửa thêm khi kiểm tra:** trang mẫu `sample-page` và bài "Hello world" mặc định của WordPress vẫn công khai. Đây là nội dung mỏng không liên quan, có trong sitemap. Đã chuyển vào thùng rác; `/sample-page/` giờ trả 404.

**Còn tồn tại:**

- Danh mục `uncategorized` trống vẫn có trang. Nên ẩn khỏi sitemap hoặc xoá sau khi kiểm tra không còn sản phẩm nào gắn vào.
- Chưa có dữ liệu có cấu trúc riêng ngoài phần WooCommerce tự in (Product, Offer). Nên thêm `Organization` có thông tin người bán ở chân trang.

## Bước 2 — Bản đồ nội dung từ 18 từ khoá

Nguồn: `02-dataset/keywords_seed-daxuly.csv` (lượng tìm kiếm là **dữ liệu mẫu**). Cột "Trang hiện có" đối chiếu với site ngày 11/10.

| # | Từ khoá | Ý định | Lượt/tháng | Cạnh tranh | Trang cần có | Trang hiện có | Việc cần làm |
|---|---|---|---:|---|---|---|---|
| 1 | cách chọn ba lô chống gù cho học sinh | Thông tin | 2.900 | Thấp | Bài hướng dẫn | Chưa có | **Ưu tiên 1** |
| 2 | ba lô chống gù loại nào tốt | So sánh | 1.600 | TB | Bài so sánh | Chưa có | **Ưu tiên 1** (gộp với #1 thành cụm ba lô) |
| 3 | mua ba lô chống gù lớp 1 | Giao dịch | 880 | Cao | Trang sản phẩm | `/product-category/ba-lo-tui/ba-lo/` | Thêm mô tả danh mục nhắc "chống gù", "lớp 1" |
| 4 | bút gel viết trơn không nhoè | So sánh | 720 | Thấp | Bài so sánh | Chưa có | Ưu tiên 3 |
| 5 | giấy kiểm định đồ dùng học tập là gì | Thông tin | 210 | Thấp | Bài giải thích | Chưa có | **Ưu tiên 2** (trụ cột định vị) |
| 6 | sáp màu không độc cho trẻ | Thông tin | 1.100 | Thấp | Bài mẹo | Chưa có | Ưu tiên 3 |
| 7 | bộ đồ dùng học tập lớp 1 đầy đủ | Giao dịch | 2.400 | Cao | Trang combo | Chưa có (chỉ có bộ lọc khối lớp) | Tạo sản phẩm nhóm "Bộ lớp 1" |
| 8 | vở ô ly lớp 1 chuẩn bộ giáo dục | Giao dịch | 1.900 | TB | Trang danh mục | `/product-category/vo-so/vo/` | Viết mô tả danh mục |
| 9 | so sánh sáp màu và bút chì màu | So sánh | 390 | Thấp | Bài so sánh | Chưa có | Sau |
| 10 | đồ dùng học tập giá sỉ | Giao dịch | 3.100 | Cao | Danh mục | — | **Không làm**: cửa hàng chỉ bán lẻ (đưa vào từ khoá phủ định, bài 6) |
| 11 | cách bọc vở cho con đẹp | Thông tin | 5.400 | Thấp | Bài hướng dẫn | Chưa có | Sau: lượng lớn nhưng ý định xa sản phẩm |
| 12 | hộp bút nào bền nhất | So sánh | 260 | Thấp | Bài tổng hợp | Chưa có | Sau |
| 13 | mua đồ dùng học tập online uy tín | Giao dịch | 1.300 | Cao | Trang chủ / Giới thiệu | Trang chủ (có thông tin người bán, chính sách) | Thêm trang "Về chúng tôi" |
| 14 | danh sách đồ dùng học tập lớp 2 | Thông tin | 1.750 | TB | Bài checklist | Chưa có | **Ưu tiên 2** (nối với bộ lọc khối lớp) |
| 15 | bút chì 2B hay HB tốt hơn | So sánh | 480 | Thấp | Bài so sánh | Chưa có | Sau |
| 16 | bút chì xanh (thương hiệu) | Điều hướng | 320 | Thấp | Trang chủ | Trang chủ | Đã đáp ứng |
| 17 | bút chì xanh khuyến mãi | Điều hướng | 140 | Thấp | Trang khuyến mãi | Chưa có | Sau |
| 18 | đồ dùng học tập có kiểm định | So sánh | 170 | Thấp | Bài tư vấn | Bộ lọc `filter_kiem-dinh-an-toan` (noindex) | **Ưu tiên 2**: cần trang tĩnh có thể lập chỉ mục |

**Phát hiện:** 13/18 từ khoá chưa có trang. Toàn bộ nhóm thông tin và so sánh, tức giai đoạn nhận biết và cân nhắc, đang bỏ trống. Site hiện chỉ phục vụ người đã biết mình muốn mua gì.

## Bước 3 — Ba ưu tiên

1. **Cụm ba lô chống gù (#1, #2 → #3):** 4.500 lượt/tháng ở giai đoạn đầu, cạnh tranh thấp. Bài hướng dẫn liên kết sang bài so sánh, bài so sánh liên kết tới danh mục ba lô (đã có). Đây cũng là trang đích cho tầng tiếp thị lại 3 (bài 6). Chỉ số: lượt truy cập tự nhiên và tỉ lệ nhấp sang danh mục, đo bằng `src` organic trong bảng sự kiện.
2. **Trụ cột "kiểm định an toàn" (#5, #18) và checklist theo lớp (#14):** gắn trực tiếp với định vị, cạnh tranh thấp. Trang `/do-dung-hoc-tap-co-kiem-dinh/` là trang tĩnh **được lập chỉ mục**, liệt kê sản phẩm đã kiểm định, thay cho URL bộ lọc đang bị noindex.
3. **Mô tả danh mục giao dịch (#3, #8) và trang combo lớp 1 (#7):** tốn ít công vì trang đã có, chỉ cần thêm 100–150 chữ mô tả hữu ích và tạo một sản phẩm nhóm.

**Không làm:** #10 (giá sỉ) vì sai mô hình kinh doanh; #11 dù lượng lớn nhất vì người tìm cách bọc vở hiếm khi mua đồ dùng học tập ngay.
