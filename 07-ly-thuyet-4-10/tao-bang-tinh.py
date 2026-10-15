"""Tao bang tinh co cong thuc lien ket cho bai thuc hanh ly thuyet 4, 6, 7, 10 (Nhom 25 - But Chi Xanh).
Doi o mau vang (gia dinh) thi moi ket qua doi theo.
Chay: python 07-ly-thuyet-4-10/tao-bang-tinh.py   ->  07-ly-thuyet-4-10/bang-tinh-ly-thuyet-4-10.xlsx
"""
import csv
import pathlib

from openpyxl import Workbook
from openpyxl.styles import Alignment, Font, PatternFill
from openpyxl.utils import get_column_letter

ROOT = pathlib.Path(__file__).resolve().parent
OUT = ROOT / "bang-tinh-ly-thuyet-4-10.xlsx"
PRODUCTS = ROOT.parent / "02-dataset" / "products.csv"

YELLOW = PatternFill("solid", fgColor="FFF2CC")   # o gia dinh - doi duoc
HEAD = PatternFill("solid", fgColor="1F4E79")
BOLD = Font(bold=True)
WHITE = Font(bold=True, color="FFFFFF")
VND = '#,##0" đ"'
PCT = "0.0%"

wb = Workbook()


def sheet(title, headers=None, widths=None):
    ws = wb.create_sheet(title)
    if headers:
        ws.append(headers)
        for c in ws[ws.max_row]:
            c.font, c.fill = WHITE, HEAD
            c.alignment = Alignment(wrap_text=True, vertical="center")
    for i, w in enumerate(widths or [], 1):
        ws.column_dimensions[get_column_letter(i)].width = w
    return ws


def note(ws, text):
    ws.append([])
    ws.append([text])
    ws.cell(ws.max_row, 1).font = Font(italic=True, color="7F7F7F")


# ---------------------------------------------------------------- 0. Gia dinh dung chung
ws = wb.active
ws.title = "GiaDinh"
for col, w in zip("ABCD", (52, 16, 14, 70)):
    ws.column_dimensions[col].width = w
ws.append(["Giả định dùng chung (ô vàng: đổi được, mọi sheet tự tính lại)", "Giá trị", "Đơn vị", "Nguồn / căn cứ"])
for c in ws[1]:
    c.font, c.fill = WHITE, HEAD
G = {}  # ten -> dia chi o


def g(key, label, value, unit, source, fmt=None):
    ws.append([label, value, unit, source])
    r = ws.max_row
    ws.cell(r, 2).fill = YELLOW
    if fmt:
        ws.cell(r, 2).number_format = fmt
    G[key] = f"GiaDinh!$B${r}"


g("aov", "Giá trị đơn trung bình", 434252, "đ", "Dữ liệu mẫu: 579.727.000đ / 1.335 đơn (bao-cao-buoi-13.md mục 3.2)", VND)
g("margin", "Tỷ lệ lợi nhuận gộp bình quân có trọng số", 0.3423, "", "Báo cáo buổi 13 mục 3.1", PCT)
g("ship", "Phí vận chuyển cửa hàng chịu cho một đơn (bình quân 5 vùng, đơn > 1 kg)", 31600, "đ", "Trung bình phí > 1 kg của 5 vùng trong setup-shipping.php; GIẢ ĐỊNH phí hợp đồng = phí niêm yết", VND)
g("free_threshold", "Ngưỡng miễn phí vận chuyển", 499000, "đ", "nhom25-shipping.php", VND)
g("pack", "Chi phí đóng gói một đơn", 5000, "đ", "GIẢ ĐỊNH - nhóm cập nhật theo báo giá hộp, băng keo", VND)
g("return_rate", "Tỷ lệ đơn bị hoàn", 0.03, "", "GIẢ ĐỊNH - chưa có dữ liệu thật", PCT)
g("return_cost", "Chi phí cho một đơn hoàn (ship 2 chiều + xử lý)", 2 * 31600 + 5000, "đ", "= 2 × phí ship + đóng gói", VND)
g("ltv", "Giá trị vòng đời khách (LTV)", 992329, "đ", "Báo cáo buổi 13 mục 3.2 (giả định 1,5 năm gắn bó)", VND)
g("ltv_cac", "Tỷ lệ LTV / CAC tối thiểu", 3, "lần", "Lý thuyết buổi 5, slide 30", None)
g("cvr_search", "Tỷ lệ chuyển đổi phiên → đơn: tìm kiếm trả phí", 0.0307, "", "Dữ liệu mẫu, báo cáo buổi 13 mục 2.2", "0.00%")
g("cvr_social", "Tỷ lệ chuyển đổi phiên → đơn: quảng cáo MXH", 0.0142, "", "Dữ liệu mẫu, báo cáo buổi 13 mục 2.2", "0.00%")
g("cpc_search_mid", "CPC tham khảo giữa khoảng: tìm kiếm", 3186, "đ", "Dữ liệu mẫu ad_spend.csv (nền tảng báo). CẦN tra Google Keyword Planner, ghi ngày tra", VND)
g("cpc_social_mid", "CPC tham khảo giữa khoảng: MXH", 1396, "đ", "Dữ liệu mẫu ad_spend.csv (nền tảng báo). CẦN tra công cụ của nền tảng, ghi ngày tra", VND)
g("cpc_spread", "Biên độ khoảng CPC (± so với giữa khoảng)", 0.3, "", "GIẢ ĐỊNH ±30% khi chưa tra được khoảng thật", PCT)
g("ad_budget", "Ngân sách thử nghiệm mỗi kênh / tháng", 10000000, "đ", "GIẢ ĐỊNH", VND)
note(ws, "Ghi chú: mọi số GIẢ ĐỊNH phải ghi rõ trong báo cáo; khi có số thật chỉ cần sửa ô vàng.")

# ---------------------------------------------------------------- San pham (de chon san pham chu luc)
sp = sheet("SanPham", ["SKU", "Tên sản phẩm", "Nhóm hàng", "Giá bán", "Giá vốn", "Biên gộp", "Khối lượng (g)"], [14, 34, 28, 12, 12, 10, 12])
with open(PRODUCTS, encoding="utf-8-sig") as f:
    for row in csv.DictReader(f):
        sp.append([row["sku"], row["ten_san_pham"], row["danh_muc"].split(">")[0].strip(), int(row["gia_ban"]), int(row["gia_von"]), None, float(row["trong_luong_g"])])
        r = sp.max_row
        sp.cell(r, 6).value = f"=(D{r}-E{r})/D{r}"
        sp.cell(r, 4).number_format = sp.cell(r, 5).number_format = VND
        sp.cell(r, 6).number_format = PCT
note(sp, "Nguồn: 02-dataset/products.csv (dữ liệu mẫu). Lưu ý: cột khối lượng của dữ liệu mẫu rất lớn (bút gel 767 g), nên gần như mọi đơn rơi vào mức phí > 1 kg.")

# ---------------------------------------------------------------- LT4: chi phi 3 muc don
c4 = sheet("LT4_ChiPhi", ["Phương thức", "Phí cố định (đ)", "Tỷ lệ phí", "Nguồn biểu phí (tra cứu, ghi ngày)", "Đơn 150.000đ", "Đơn 450.000đ", "Đơn 1.500.000đ"], [26, 14, 10, 52, 16, 16, 16])
c4.append(["Giá trị đơn", None, None, None, 150000, 450000, 1500000])
c4.append(["Lợi nhuận gộp của đơn", None, None, None, f"=E2*{G['margin']}", f"=F2*{G['margin']}", f"=G2*{G['margin']}"])
methods = [
    ("VNPay (thẻ ATM nội địa)", 1650, 0.011, "GIẢ ĐỊNH - CẦN tra biểu phí VNPay trên hợp đồng/website, ghi ngày tra"),
    ("VietQR (chuyển khoản)", 0, 0.0, "Chuyển khoản liên ngân hàng 24/7 thường miễn phí cho người nhận - CẦN xác nhận với ngân hàng"),
    ("COD (thu hộ qua đơn vị vận chuyển)", 0, 0.008, "GIẢ ĐỊNH - CẦN tra phí thu hộ của GHN/GHTK/Viettel Post"),
]
for name, fixed, rate, src in methods:
    c4.append([name, fixed, rate, src])
    r = c4.max_row
    c4.cell(r, 2).fill = c4.cell(r, 3).fill = YELLOW
    c4.cell(r, 3).number_format = "0.00%"
    for col in "EFG":
        c4[f"{col}{r}"] = f"=$B{r}+$C{r}*{col}$2"
        c4[f"{col}{r}"].number_format = VND
c4.append([])
c4.append(["Tỷ trọng phí / lợi nhuận gộp"])
c4.cell(c4.max_row, 1).font = BOLD
for i, (name, *_rest) in enumerate(methods):
    r_fee = 4 + i
    c4.append([name])
    r = c4.max_row
    for col in "EFG":
        c4[f"{col}{r}"] = f"={col}{r_fee}/{col}$3"
        c4[f"{col}{r}"].number_format = PCT
for row in c4.iter_rows(min_row=2, max_row=3, min_col=5, max_col=7):
    for c in row:
        c.number_format = VND
note(c4, "Công thức: phí = phí cố định + tỷ lệ × giá trị đơn; tỷ trọng = phí ÷ lợi nhuận gộp (lý thuyết buổi 4, slide 31). Với đơn nhỏ, phần cố định chiếm tỷ trọng lớn.")

# ---------------------------------------------------------------- LT4: doi soat 3 nguon
d4 = sheet("LT4_DoiSoat", ["Mã đơn", "Phương thức", "Số tiền theo cửa hàng", "Số tiền theo cổng thanh toán", "Phí cổng", "Thực nhận theo sao kê", "Chênh lệch cửa hàng − cổng", "Chênh lệch cổng − phí − sao kê", "Kết luận", "Nguyên nhân và hướng xử lý"], [10, 12, 14, 14, 10, 14, 14, 16, 12, 70])
rows = [
    ("DH201", "VNPay", 348000, 348000, None, 342522, "Khớp."),
    ("DH202", "VNPay", 458000, 916000, None, 901924, "Giao dịch trùng: khách bấm thanh toán 2 lần, cổng ghi nhận 2 lần. Xử lý: hoàn lần thứ hai qua cổng; IPN mới đã chặn đơn thứ hai (mã 02)."),
    ("DH203", "VNPay", 520000, 468000, None, 461202, "Lệch số tiền: mã giảm giá 10% áp sau khi cấp phép. Xử lý: rà soát chứng từ, điều chỉnh bút toán; IPN mới từ chối số tiền không khớp (mã 04)."),
    ("(không có)", "VietQR", 0, 290000, 0, 290000, "Có tiền, không đơn: khách chuyển khoản nhưng đơn không tạo. Xử lý: truy vết theo nội dung chuyển khoản, liên hệ khách."),
    ("DH204", "VietQR", 415000, 0, 0, 0, "Có đơn, không có tiền: khách chưa chuyển. Xử lý: giữ đơn Tạm giữ, không giao, hủy sau 24 giờ."),
]
vnp_row = 4  # dong VNPay trong LT4_ChiPhi
for code, method, shop, gw, fee, bank, why in rows:
    d4.append([code, method, shop, gw, fee, bank])
    r = d4.max_row
    if fee is None:
        d4[f"E{r}"] = f"=IF(D{r}=0,0,LT4_ChiPhi!$B${vnp_row}*IF(D{r}>C{r}*1.5,2,1)+LT4_ChiPhi!$C${vnp_row}*D{r})"
    d4[f"G{r}"] = f"=C{r}-D{r}"
    d4[f"H{r}"] = f"=D{r}-E{r}-F{r}"
    d4[f"I{r}"] = f'=IF(AND(ABS(G{r})<1,ABS(H{r})<1),"Khớp","LỆCH")'
    d4[f"J{r}"] = why
    for col in "CDEFGH":
        d4[f"{col}{r}"].number_format = VND
note(d4, "DỮ LIỆU GIẢ LẬP để luyện đối soát 3 nguồn (lý thuyết buổi 4, slide 15-16). Sandbox VNPay không có sao kê thật; khi có sao kê thử nghiệm, thay cột D và F.")

# ---------------------------------------------------------------- LT4: 3 quy tac rui ro
q4 = sheet("LT4_QuyTacRuiRo", ["Quy tắc", "Điều kiện kích hoạt (đo được)", "Ngưỡng", "Hành động", "Lý do chọn ngưỡng"], [8, 60, 14, 34, 70])
q4.append([1, "Đơn đầu tiên của một email có giá trị lớn hơn N lần giá trị đơn trung bình", 3, "Giữ đơn, gọi xác nhận trước khi giao", "Đơn trung bình 434.252đ ⇒ ngưỡng ≈ 1,3 triệu. Khách mới mua lớn bất thường là dấu hiệu dùng thẻ đánh cắp (slide 27)."])
q4.append([2, "Cùng một IP có từ N lần thanh toán bị từ chối trong 1 giờ", 3, "Chặn thanh toán thẻ của IP đó 1 giờ (vẫn cho COD)", "Dò thử số thẻ tạo nhiều lần từ chối liên tiếp (slide 26). Người thật hiếm khi bị từ chối quá 2 lần."])
q4.append([3, "Đơn COD giao tới tỉnh khác Hà Nội có giá trị từ N đồng", 1000000, "Gọi xác minh trước khi gửi hàng", "COD giá trị lớn đi xa có chi phí hoàn cao (2 × phí ship ≈ 63.000đ) và chiếm dụng vốn (slide 12, 42)."])
for r in range(2, 5):
    q4[f"C{r}"].fill = YELLOW
q4["C4"].number_format = VND
q4.append([])
q4.append(["Kiểm tra nhanh quy tắc 1:", f'="Ngưỡng đơn đầu tiên = "&TEXT(C2*{G["aov"]},"#,##0")&" đ"'])

# ---------------------------------------------------------------- LT6: nguong chi
n6 = sheet("LT6_NguongChi", ["Chỉ số", "Giá trị", "Công thức / ghi chú"], [58, 16, 80])
n6.append(["Sản phẩm chủ lực (số dòng trong sheet SanPham)", 30, "Đổi số dòng để tính cho sản phẩm khác. Mặc định: Ba lô có phản quang (có kiểm định, khớp định vị an toàn)"])
n6["B2"].fill = YELLOW
n6.append(["Tên sản phẩm", "=INDEX(SanPham!B:B,B2)", ""])
n6.append(["Giá bán", "=INDEX(SanPham!D:D,B2)", ""])
n6.append(["Giá vốn", "=INDEX(SanPham!E:E,B2)", "Giá nhập, chưa gồm vận chuyển"])
n6.append(["Biên gộp sản phẩm (chưa tính vận chuyển)", "=(B4-B5)/B4", "(Giá bán − Giá vốn) ÷ Giá bán"])
n6.append(["Giá trị đơn dùng để tính", f"={G['aov']}", "Mặc định = đơn trung bình (đơn có ba lô thường kèm món khác). Ô vàng, đổi được"])
n6["B7"].fill = YELLOW
n6.append(["Chi phí vận chuyển + đóng gói + hoàn hàng của một đơn", f"=IF(B7>={G['free_threshold']},{G['ship']},0)+{G['pack']}+{G['return_rate']}*{G['return_cost']}", "Phí ship cửa hàng chỉ chịu khi đơn ≥ ngưỡng miễn phí; dưới ngưỡng khách trả"])
n6.append(["Biên gộp thực", "=B6-B8/B7", "Biên gộp sản phẩm − chi phí logistics tính trên giá trị đơn (slide 26: nêu rõ chi phí nào tính vào giá vốn)"])
n6.append(["ROAS hoà vốn (sản phẩm chủ lực)", "=1/B9", "= 1 ÷ biên gộp thực"])
n6.append(["ROAS hoà vốn (bình quân cửa hàng)", f"=1/{G['margin']}", "= 1 ÷ 34,23% (dùng cho dự báo 3 tình huống theo đơn trung bình)"])
n6.append(["CPA tối đa theo đơn", "=B7*B9", "= giá trị đơn × biên gộp thực"])
n6.append(["CPA tối đa theo LTV (CAC tối đa)", f"={G['ltv']}/{G['ltv_cac']}", "= LTV ÷ 3 (buổi 5); chấp nhận dưới ngưỡng ở đơn đầu nếu khách mua lại"])
n6.append([])
n6.append(["Kênh", "CPC tối đa theo đơn", "CPC tối đa theo LTV", "CPC tham khảo giữa khoảng", "Khả thi theo đơn?", "Khả thi theo LTV?"])
for c in n6[n6.max_row]:
    c.font = BOLD
for name, cvr, cpc in (("Tìm kiếm trả phí", "cvr_search", "cpc_search_mid"), ("Quảng cáo MXH", "cvr_social", "cpc_social_mid")):
    n6.append([name, f"=$B$12*{G[cvr]}", f"=$B$13*{G[cvr]}", f"={G[cpc]}"])
    r = n6.max_row
    n6[f"E{r}"] = f'=IF(D{r}<=B{r},"Có","Không - tối ưu trang đích trước")'
    n6[f"F{r}"] = f'=IF(D{r}<=C{r},"Có","Không")'
n6.column_dimensions["D"].width = 18
n6.column_dimensions["E"].width = 30
n6.column_dimensions["F"].width = 16
for r in range(2, n6.max_row + 1):
    for col in "BCD":
        v = n6[f"{col}{r}"]
        if r in (6, 9):
            v.number_format = PCT
        elif r in (10, 11):
            v.number_format = "0.00"
        elif r != 2:
            v.number_format = VND
note(n6, "Hai cách tính ngưỡng cho ra kết luận khác nhau: theo đơn đầu (chặt) và theo LTV (nới). Báo cáo phải chọn và lập luận (lý thuyết buổi 6, slide 11).")

# ---------------------------------------------------------------- LT6: 3 tinh huong
s6 = sheet("LT6_3TinhHuong", ["Kênh", "Tình huống", "CPC", "CVR", "Ngân sách", "Lượt nhấp", "Đơn", "Doanh thu", "ROAS", "CPA", "LN gộp sau quảng cáo", "Trong ngưỡng hoà vốn?"], [16, 12, 10, 9, 14, 11, 8, 14, 8, 12, 16, 22])
for name, cvr, cpc in (("Tìm kiếm trả phí", "cvr_search", "cpc_search_mid"), ("Quảng cáo MXH", "cvr_social", "cpc_social_mid")):
    for label, cpc_k, cvr_k in (("Bất lợi", f"(1+{G['cpc_spread']})", "0.8"), ("Cơ sở", "1", "1"), ("Thuận lợi", f"(1-{G['cpc_spread']})", "1.2")):
        s6.append([name, label])
        r = s6.max_row
        s6[f"C{r}"] = f"={G[cpc]}*{cpc_k}"
        s6[f"D{r}"] = f"={G[cvr]}*{cvr_k}"
        s6[f"E{r}"] = f"={G['ad_budget']}"
        s6[f"F{r}"] = f"=E{r}/C{r}"
        s6[f"G{r}"] = f"=F{r}*D{r}"
        s6[f"H{r}"] = f"=G{r}*{G['aov']}"
        s6[f"I{r}"] = f"=H{r}/E{r}"
        s6[f"J{r}"] = f"=E{r}/G{r}"
        s6[f"K{r}"] = f"=H{r}*{G['margin']}-E{r}"
        s6[f"L{r}"] = f'=IF(I{r}>=LT6_NguongChi!$B$11,"Có","KHÔNG - giảm ngân sách/hoãn")'
        for col, fmt in (("C", VND), ("D", "0.00%"), ("E", VND), ("F", "#,##0"), ("G", "0.0"), ("H", VND), ("I", "0.00"), ("J", VND), ("K", VND)):
            s6[f"{col}{r}"].number_format = fmt
note(s6, "Bất lợi: CPC ở mức cao của khoảng, CVR thấp hơn hiện tại 20%. Thuận lợi: CPC thấp, CVR cao hơn 20% (lý thuyết buổi 6, slide 27). Doanh thu tính theo đơn trung bình.")

# ---------------------------------------------------------------- LT7: bang danh gia rui ro
r7 = sheet("LT7_RuiRo", ["Tài sản", "Lỗ hổng / mối đe doạ", "Khả năng (1-5)", "Tác động (1-5)", "Điểm", "Xếp hạng", "Lý do cho điểm", "Biện pháp / trạng thái 11/10"], [24, 44, 9, 9, 7, 8, 60, 60])
risks = [
    ("Khoá riêng TLS", "Đã nằm trong lịch sử git công khai trên GitHub", 5, 3, "Ai cũng tải được từ lịch sử repo (khả năng 5). Chứng chỉ tự ký chỉ dùng cục bộ nên tác động trung bình (3).", "Đã gỡ khỏi git, sinh khoá mới bằng certs/tao-chung-chi.sh. Khoá cũ coi như đã lộ."),
    ("Khoá bí mật VNPay sandbox", "Đã nằm trong lịch sử git công khai (setup-payments.php, db-init.sql)", 5, 4, "Lộ công khai (5). Có khoá thì ký được thông báo IPN giả để đơn thành 'đã thanh toán' (4).", "Đã chuyển sang .env, xoá khỏi mã và CSDL. CẦN đổi khoá mới trên cổng sandbox."),
    ("Tài khoản quản trị", "Mật khẩu yếu admin123 ghi trong README công khai", 5, 5, "Bị dò tự động hằng ngày (5). Chiếm quyền là mất toàn bộ cửa hàng và dữ liệu khách (5).", "Đã đổi mật khẩu 20 ký tự, bỏ khỏi README; có MFA và khoá sau 5 lần sai."),
    ("Plugin WooCommerce 9.7.1", "Chậm vá: đã có bản 10.9.4", 4, 5, "Lỗ hổng đã công bố bị quét tự động nhanh (4). Lỗ hổng trong lõi bán hàng ảnh hưởng thanh toán và dữ liệu (5).", "CHƯA LÀM: nâng cấp sau khi sao lưu và thử trên bản sao."),
    ("Bản sao lưu", "Không mã hoá, nằm cùng máy, không có bản ngoại tuyến", 3, 5, "Máy bị mã độc tống tiền thì mất cả bản gốc lẫn bản sao (3). Mất toàn bộ đơn và khách (5).", "Đã thử phục hồi (3 giây, khớp 100%). CẦN: chép ra ổ ngoài, mã hoá, lịch tự động."),
    ("db-init.sql trong repo công khai", "Bản dump toàn bộ CSDL (đơn mẫu, mã băm mật khẩu)", 4, 3, "Ai cũng tải được (4). Dữ liệu là mẫu, mật khẩu đã băm (3).", "Đã xoá khoá VNPay, cập nhật mã băm mật khẩu mới. CẦN cân nhắc chỉ giữ dữ liệu mẫu tối thiểu."),
    ("Plugin VNPay (bên thứ ba)", "Tin phản hồi trình duyệt để xác nhận thanh toán; so chữ ký bằng ==", 4, 5, "Lỗi logic phổ biến, dễ khai thác (4). Nhận hàng không trả tiền (5).", "Đã sửa: chỉ IPN được đổi trạng thái, hash_equals, khớp số tiền; 6/6 kiểm thử đạt."),
    ("Trang quản trị", "Một tài khoản quản trị duy nhất dùng chung cả nhóm", 3, 4, "Không truy vết được ai làm gì (3); mất một máy là lộ quyền cao nhất (4).", "CẦN: mỗi thành viên một tài khoản Quản lý cửa hàng; tài khoản riêng cho giảng viên (TH15)."),
    ("Nhật ký WooCommerce (wc-logs)", "Có thể chứa mã đơn, số tiền; từng bị đưa lên git", 2, 3, "Thư mục chặn truy cập web (403) (2). Lộ thông tin giao dịch (3).", "Đã gỡ khỏi git và gitignore."),
    ("Biểu mẫu lọc sản phẩm", "Tham số GET đưa vào truy vấn (chèn mã, kịch bản chéo trang)", 2, 4, "WooCommerce dùng truy vấn tham số hoá, rủi ro thấp (2). Nếu lọt thì đọc được CSDL (4).", "CẦN rà soát mã product-filters.php: làm sạch và escape mọi tham số."),
    ("Máy chủ trong mùa tựu trường", "Quá tải hoặc từ chối dịch vụ đúng tháng 8", 2, 4, "Cửa hàng nhỏ ít bị nhắm (2). Ngừng bán đúng mùa cao điểm (4).", "Đã có nén, cache trình duyệt. Khi triển khai thật: CDN, giới hạn tần suất."),
    ("Email khách hàng", "Lừa đảo giả danh cửa hàng (thiếu SPF/DKIM)", 2, 3, "Cửa hàng chưa nổi tiếng (2). Mất uy tín, khách bị lừa (3).", "Khi có tên miền thật: SPF, DKIM, DMARC."),
]
for asset, vuln, l, i, why, fix in risks:
    r7.append([asset, vuln, l, i, None, None, why, fix])
    r = r7.max_row
    r7[f"C{r}"].fill = r7[f"D{r}"].fill = YELLOW
    r7[f"E{r}"] = f"=C{r}*D{r}"
    r7[f"F{r}"] = f"=RANK(E{r},$E$2:$E${len(risks) + 1})"
    for col in "GH":
        r7[f"{col}{r}"].alignment = Alignment(wrap_text=True, vertical="top")
note(r7, "Điểm = khả năng × tác động (lý thuyết buổi 7, slide 23). Khả năng: lỗ hổng có phổ biến và bị tấn công tự động nhắm tới không. Tác động: tài chính, uy tín, nghĩa vụ pháp lý.")

# ---------------------------------------------------------------- LT10: chi so logistics
l10 = sheet("LT10_Logistics", ["Chỉ số", "Cách đo", "Giá trị hiện tại", "Mục tiêu", "Cho biết vấn đề ở đâu"], [34, 56, 16, 14, 56])
l10.append(["Thời gian xử lý đơn (giờ)", "Từ lúc đơn sang Đang xử lý tới lúc bàn giao vận chuyển (ghi chú đơn)", "Chưa đo", 24, "Nội bộ - cửa hàng kiểm soát hoàn toàn"])
l10.append(["Thời gian giao hàng (ngày)", "Từ bàn giao tới khách nhận (mã vận đơn)", "Chưa đo", 4, "Đối tác vận chuyển"])
l10.append(["Tỷ lệ giao thành công lần đầu", "Số đơn giao được ngay ÷ số đơn giao", "Chưa đo", 0.95, "Kiểm tra địa chỉ khi đặt, gọi trước giờ giao"])
l10.append(["Tỷ lệ hoàn hàng (tách theo nguyên nhân)", "Đơn hoàn ÷ đơn giao; nguyên nhân: lỗi / sai mô tả / đổi ý (theo chính sách đổi trả)", f"={G['return_rate']}", 0.03, "Hàng lỗi → chất lượng; sai mô tả → trang sản phẩm"])
l10.append(["Độ chính xác đơn hàng", "Đơn đúng sản phẩm, số lượng ÷ tổng đơn", "Chưa đo", 0.99, "Khâu chọn và đóng gói"])
l10.append(["Chi phí logistics mỗi đơn", "Ship cửa hàng chịu + đóng gói + tỷ lệ hoàn × chi phí hoàn", f"={G['ship']}*IF({G['aov']}>={G['free_threshold']},1,0)+{G['pack']}+{G['return_rate']}*{G['return_cost']}", "", "So với lợi nhuận gộp mỗi đơn (dòng dưới)"])
l10.append(["Lợi nhuận gộp mỗi đơn trung bình", "Đơn trung bình × biên gộp", f"={G['aov']}*{G['margin']}", "", ""])
l10.append(["Chi phí logistics / lợi nhuận gộp", "", "=C7/C8", 0.3, "Vượt 100% thì bán càng nhiều càng lỗ (slide 8)"])
l10.append(["Ngưỡng miễn phí / đơn trung bình", "", f"={G['free_threshold']}/{G['aov']}", "1,1–1,2", "Lý thuyết buổi 10 slide 9; hướng dẫn TH12 bước 12.2"])
for r in (4, 5, 6, 9):
    l10[f"C{r}"].number_format = l10[f"D{r}"].number_format = PCT
for r in (7, 8):
    l10[f"C{r}"].number_format = VND
l10["C10"].number_format = "0.00"
note(l10, "Các chỉ số 'Chưa đo' cần dữ liệu vận hành thật; cửa hàng thử nghiệm chưa có đơn vị vận chuyển. Chi phí logistics tính theo giả định ở sheet GiaDinh.")

wb.save(OUT)
print("Da tao", OUT)
