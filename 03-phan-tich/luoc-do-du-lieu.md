# Lược đồ dữ liệu sản phẩm — Cửa hàng Bút Chì Xanh

## 1. Mã sản phẩm

**Quy tắc:** `[NHÓM]-[LOẠI]-[Số thứ tự]`

| Mã nhóm | Ý nghĩa | Mã loại | Ý nghĩa |
|---|---|---|---|
| BUT | Bút viết | GEL | Bút gel |
| VO | Vở & sổ | CHI | Bút chì |
| DC | Dụng cụ | MUC | Bút mực / highlight |
| BL | Ba lô & túi | VO | Vở |
| MT | Mỹ thuật | SO | Sổ |
| | | GIAY | Giấy |
| | | VE | Dụng cụ vẽ |
| | | HT | Dụng cụ học tập |
| | | BL | Ba lô |
| | | TUI | Túi |
| | | MAU | Màu vẽ |
| | | DCMT | Dụng cụ mỹ thuật |

**Ví dụ:** BUT-GEL-001 = Bút viết – Bút gel – số 001

**Lý do:** Mã mang thông tin phân loại, không phải số tự tăng. Khi nhập lại dữ liệu, mã không thay đổi.

## 2. Nhóm trường

### 2.1. Bắt buộc để bán

| Trường | Kiểu dữ liệu | Ví dụ | Vì sao cần |
|---|---|---|---|
| sku | VARCHAR(20) | BUT-GEL-001 | Định danh duy nhất, tra cứu đơn hàng |
| ten_san_pham | VARCHAR(200) | Bút bi gel 0.5mm | Hiển thị cho khách |
| gia_ban | DECIMAL(10,0) | 58000 | Tính tiền, hiển thị giá |
| anh | VARCHAR(255) | img/but-gel-001.webp | Ảnh sản phẩm |
| ton_kho | INT | 97 | Kiểm tra còn hàng |

### 2.2. Bắt buộc để phân tích

| Trường | Kiểu dữ liệu | Ví dụ | Vì sao cần |
|---|---|---|---|
| danh_muc | VARCHAR(100) | Bút viết > Bút bi & ruột bút | Phân loại, phân trang, bộ lọc |
| gia_von | DECIMAL(10,0) | 39000 | Tính lợi nhuận gộp, ROAS (buổi 13) |
| kiem_dinh_an_toan | ENUM('Co','Khong') | Co | Bộ lọc theo an toàn |
| khoi_lop | VARCHAR(20) | Lớp 1-3 | Bộ lọc theo độ tuổi |
| loai_san_pham | VARCHAR(50) | Bút bi | Bộ lọc theo loại |

### 2.3. Nên có

| Trường | Kiểu dữ liệu | Ví dụ | Vì sao cần |
|---|---|---|---|
| trong_luong_g | DECIMAL(8,1) | 0.767 | Tính phí vận chuyển |
| kich_thuoc | VARCHAR(30) | 14 x 0.7 x 0.7 cm | Tính phí vận chuyển |
| mo_ta_ngan | VARCHAR(255) | Bút bi gel 0.5mm mực đều... | Hiển thị trong danh sách |
| mo_ta_dai | TEXT | (mô tả chi tiết) | SEO, thông tin đầy đủ |
| tu_khoa | VARCHAR(200) | bút bi gel, bút viết... | Tối ưu tìm kiếm |

## 3. Cây danh mục

```
Bút viết → Bút bi & ruột bút / Bút chì & mực
Vở & sổ → Vở / Sổ & giấy
Dụng cụ → Dụng cụ vẽ / Dụng cụ học tập
Ba lô & túi → Ba lô / Túi
Mỹ thuật → Màu vẽ / Dụng cụ mỹ thuật
```

- 5 danh mục lớp 1 (tối đa 5–7)
- 2 nhánh con mỗi nhóm
- Tới sản phẩm trong 2 lần bấm từ trang chủ
- Tên danh mục có căn cứ từ dữ liệu tìm kiếm (keywords_seed-daxuly.csv)

## 4. Bộ lọc

| Thuộc tính | Giá trị | Căn cứ |
|---|---|---|
| Khoảng giá | < 50k, 50–150k, 150–300k, > 300k | Khách dùng để loại trừ |
| Kiểm định an toàn | Có / Không | Quan trọng cho phụ huynh |
| Khối lớp | Lớp 1, Lớp 2, Lớp 1-3, Lớp 4-5, Mọi lớp | Từ tên sản phẩm |
| Loại sản phẩm | Bút bi, Bút chì, Vở, Sổ, Ba lô... | Phân loại nhanh |

**Không đưa vào bộ lọc:** trọng lượng, kích thước (khách văn phòng phẩm hiếm khi lọc theo thuộc tính này; chỉ dùng để tính phí vận chuyển).

## 5. Liên kết mềm

SKU035 (Bút chì màu 36 màu) thuộc danh mục chính **Mỹ thuật > Màu vẽ**, đồng thời liên kết mềm sang **Bút viết > Bút chì & mực** (sản phẩm thuộc nhiều danh mục).

## 6. Tệp nguồn

- `02-dataset/products.csv` — dữ liệu đầy đủ 15 cột
- `02-dataset/woocommerce-import.csv` — tệp nhập WooCommerce (26 cột, format chuẩn)
- `01-cua-hang/img/*.webp` — 40 ảnh đã nén (trung bình 4KB/ảnh)
