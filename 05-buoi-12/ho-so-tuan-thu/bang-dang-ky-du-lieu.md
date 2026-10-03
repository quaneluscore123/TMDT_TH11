# Bảng đăng ký dữ liệu cá nhân — Bút Chì Xanh

Phiên bản 1.0 · lập ngày 02/10/2026 · Nhóm 25 · Bảng này là **nguồn duy nhất** cho Chính sách quyền riêng tư: mọi dòng trong chính sách phải có ở đây và ngược lại.

**Căn cứ pháp lý được dùng trong bảng**

| Ký hiệu | Cơ sở | Ghi chú |
|---|---|---|
| **HĐ** | Cần để thực hiện hợp đồng mua bán với chính người mua | Không có dữ liệu này thì không giao được hàng |
| **PL** | Nghĩa vụ pháp lý của người bán (kế toán, thuế, giải quyết khiếu nại của người tiêu dùng) | Luật Kế toán, Luật Bảo vệ quyền lợi người tiêu dùng 2023 |
| **ĐY** | Sự đồng ý của chủ thể dữ liệu, rút lại được bất kỳ lúc nào | Luật Bảo vệ dữ liệu cá nhân 2025 |
| **LI** | Bảo vệ an toàn hệ thống và chống gian lận (cần thiết để vận hành an toàn) | Thu tối thiểu, lưu ngắn |

## 1. Các nơi thu thập đã rà soát

| # | Nơi thu thập | Có trên site? | Kết luận |
|---|---|---|---|
| A | Biểu mẫu đặt hàng (`/checkout/`) | Có | Ghi ở mục 2.A; đã **bỏ 3 trường** không ai dùng |
| B | Thanh toán VNPay / VietQR | Có | Mục 2.B; shop **không** nhận và không lưu số thẻ |
| C | Đánh giá sản phẩm | Có | Mục 2.C; đã **bỏ** lưu IP và tắt Gravatar |
| D | Tra cứu đơn hàng (`/tra-cuu-don-hang/`) | Có | Chỉ dùng mã đơn + email để đối chiếu, không lưu thêm |
| E | Tài khoản khách hàng / đăng ký | **Không** — đăng ký đang tắt | Chỉ có tài khoản quản trị |
| F | Đăng ký nhận tin (newsletter) | **Không** có biểu mẫu | Nếu thêm sau này phải bổ sung dòng mới, cơ sở ĐY |
| G | Biểu mẫu liên hệ | **Không** — liên hệ qua điện thoại/Zalo/email | Không thu qua website |
| H | Mã theo dõi hành vi | Đã rà soát (xem Biên bản an toàn, hạng mục 10) | **Không** có Google Analytics, Facebook Pixel, Google Fonts. Đã **tắt** "Order Attribution" của WooCommerce |
| I | Cookie kỹ thuật, nhật ký máy chủ | Có | Mục 2.D |

## 2. Bảng đăng ký

### 2.A Biểu mẫu đặt hàng

| Tên trường | Mục đích thu thập | Cơ sở pháp lý | Thời gian lưu | Chia sẻ với ai |
|---|---|---|---|---|
| Họ, Tên (`billing_first_name`, `billing_last_name`) | Ghi tên người nhận trên vận đơn, xưng hô khi liên hệ | HĐ | 12 tháng sau khi đơn hoàn tất, sau đó ẩn danh tự động | Đơn vị vận chuyển |
| Địa chỉ: số nhà, đường (`billing_address_1`) | Giao hàng | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển |
| Phường/Xã (`billing_city`) | Giao hàng | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển |
| Tỉnh/Thành phố (`billing_state`) | Giao hàng **và** tính phí vận chuyển (nội thành Hà Nội / tỉnh khác) | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển |
| Quốc gia (`billing_country`) | Cố định "Việt Nam" (chỉ giao trong nước) | HĐ | Như trên | Không |
| Số điện thoại (`billing_phone`) | Shipper gọi khi giao; shop gọi xác nhận đơn COD, báo hết hàng/hoàn tiền | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển |
| Email (`billing_email`) | Gửi thư xác nhận đơn và hóa đơn; dùng cùng mã đơn để tra cứu đơn | HĐ | 12 tháng sau khi đơn hoàn tất | Không |
| Ghi chú đơn hàng (`order_comments`, không bắt buộc) | Yêu cầu riêng khi giao (giờ nhận, gọi trước…) | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển (nếu liên quan việc giao) |
| Tên, địa chỉ giao đến địa chỉ khác (`shipping_*`, không bắt buộc) | Giao cho người nhận khác người mua | HĐ | 12 tháng sau khi đơn hoàn tất | Đơn vị vận chuyển |
| Mặt hàng, số lượng, số tiền, ngày mua (không định danh sau khi ẩn danh) | Sổ sách kế toán, thuế | PL | 10 năm (chứng từ kế toán) — **đã tách khỏi tên/địa chỉ** khi ẩn danh | Cơ quan thuế khi được yêu cầu |
| Địa chỉ IP + trình duyệt của người đặt (`customer_ip_address`, `customer_user_agent`) | VNPay bắt buộc gửi IP người trả (`vnp_IpAddr`); đối chiếu khi có tranh chấp/gian lận thanh toán | LI | 12 tháng (xóa cùng khi ẩn danh đơn) | VNPay (chỉ IP) |
| ~~Tên công ty (`billing_company`)~~ | Không ai dùng — shop bán lẻ, không xuất hóa đơn doanh nghiệp | — | **ĐÃ BỎ** khỏi biểu mẫu | — |
| ~~Địa chỉ dòng 2 (`billing_address_2`)~~ | Trùng với dòng 1 | — | **ĐÃ BỎ** | — |
| ~~Mã bưu chính (`billing_postcode`)~~ | Shipper trong nước không dùng | — | **ĐÃ BỎ** | — |

> Đơn **chưa thanh toán / thất bại / đã hủy** không cần cho kế toán nên được chuyển vào thùng rác sau **1 tháng** (cấu hình WooCommerce → Cài đặt → Tài khoản & Quyền riêng tư).

### 2.B Thanh toán

| Tên trường | Mục đích thu thập | Cơ sở pháp lý | Thời gian lưu | Chia sẻ với ai |
|---|---|---|---|---|
| Số thẻ, ngày hết hạn, OTP | Thanh toán | — | **Shop không thu.** Khách nhập trực tiếp trên trang của VNPay/ngân hàng | — |
| Số tiền, mã đơn, mã tham chiếu giao dịch (`vnp_TxnRef`) | Yêu cầu thanh toán, chống ghi nhận trùng | HĐ | Cùng đơn hàng | VNPay |
| Mã giao dịch VNPay (lưu trong đơn hàng) | Đối soát, hoàn tiền | PL | 10 năm (không chứa tên/địa chỉ sau khi ẩn danh) | VNPay khi đối soát |
| Nội dung chuyển khoản VietQR "DH{mã đơn}" | Đối chiếu chuyển khoản | HĐ | Cùng đơn hàng | api.vietqr.io (chỉ nhận số tiền + mã đơn để tạo ảnh QR, **không** nhận tên/SĐT) |

### 2.C Đánh giá sản phẩm

| Tên trường | Mục đích thu thập | Cơ sở pháp lý | Thời gian lưu | Chia sẻ với ai |
|---|---|---|---|---|
| Tên hiển thị | Hiện cùng nội dung đánh giá | ĐY (khách tự gửi) | Đến khi khách yêu cầu xóa | Công khai trên trang |
| Email | Xác minh "đã mua hàng", liên hệ nếu cần | ĐY | Đến khi khách yêu cầu xóa | Không |
| ~~Địa chỉ IP người đánh giá~~ | Không ai dùng (không có bộ lọc spam) | — | **ĐÃ BỎ** (không lưu) | — |
| ~~Ảnh đại diện Gravatar~~ | Gửi mã băm email sang Gravatar (bên thứ ba) để lấy ảnh — không cần | — | **ĐÃ TẮT** | — |

### 2.D Kỹ thuật và an toàn

| Tên trường | Mục đích thu thập | Cơ sở pháp lý | Thời gian lưu | Chia sẻ với ai |
|---|---|---|---|---|
| Cookie giỏ hàng (`wp_woocommerce_session_*`, `woocommerce_cart_hash`, `woocommerce_items_in_cart`) | Ghi nhớ giỏ hàng (kể cả khi khách đóng trang giữa lúc thanh toán) | HĐ (cookie thiết yếu) | 2 ngày | Không |
| Cookie đăng nhập (`wordpress_logged_in_*`) | Chỉ cho quản trị viên | LI | Hết phiên / 14 ngày nếu chọn "ghi nhớ" | Không |
| Mã băm IP + số lần đăng nhập sai | Khóa tạm thời khi đoán mật khẩu (5 lần sai → khóa 15 phút) | LI | 15 phút | Không |
| Nhật ký truy cập máy chủ (IP, đường dẫn, trình duyệt) | Điều tra sự cố an toàn | LI | Đến khi container được tạo lại (chưa cấu hình xoay vòng — xem Biên bản an toàn) | Không |
| ~~Nguồn truy cập, UTM, thiết bị (WooCommerce Order Attribution, cookie `sbjs_*`)~~ | Không có ai được giao phân tích và site chưa có banner xin đồng ý | — | **ĐÃ TẮT** — nếu cần đo kênh phải thêm banner xin đồng ý (ĐY) trước | — |

## 3. Bên nhận dữ liệu (tổng hợp — chuyển nguyên sang chính sách quyền riêng tư)

| Bên nhận | Nhận gì | Để làm gì |
|---|---|---|
| Đơn vị vận chuyển (GHN/GHTK/Viettel Post — tùy đơn) | Tên, SĐT, địa chỉ, ghi chú giao hàng, số tiền thu hộ | Giao hàng, thu hộ COD |
| VNPay | Số tiền, mã đơn, mã tham chiếu, IP người trả | Xử lý thanh toán |
| api.vietqr.io | Số tiền, "DH{mã đơn}" | Tạo ảnh mã QR |
| Cơ quan nhà nước có thẩm quyền | Theo yêu cầu bằng văn bản | Nghĩa vụ pháp lý |

**Không** bán, không cho thuê dữ liệu; không chuyển dữ liệu ra nước ngoài ngoài các dịch vụ trên.

## 4. Thay đổi kỹ thuật để site khớp với bảng này

| Việc | Ở đâu |
|---|---|
| Bỏ trường công ty, địa chỉ 2, mã bưu chính | `wp-content/mu-plugins/nhom25-checkout-vn.php` |
| Không lưu IP người đánh giá | `wp-content/mu-plugins/nhom25-checkout-vn.php` |
| Tắt Gravatar, tắt Order Attribution | `01-cua-hang/setup-ho-so-tuan-thu.php` |
| Ẩn danh đơn hoàn tất sau 12 tháng; xóa đơn chờ/thất bại/hủy sau 1 tháng | `01-cua-hang/setup-ho-so-tuan-thu.php` (tùy chọn `woocommerce_anonymize_completed_orders`, `woocommerce_trash_*_orders`) |
| Nhật ký WooCommerce giữ 30 ngày | `01-cua-hang/setup-ho-so-tuan-thu.php` |
