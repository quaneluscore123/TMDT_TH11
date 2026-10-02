# -*- coding: utf-8 -*-
import csv, os, io, sys
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DS = os.path.join(BASE, '02-dataset')

OLD_SKU = {
    'SKU001':'BUT-GEL-001','SKU002':'BUT-CHI-002','SKU003':'BUT-MUC-003','SKU004':'BUT-MUC-004',
    'SKU005':'BUT-MUC-005','SKU006':'BUT-GEL-006','SKU007':'BUT-CHI-007','SKU008':'BUT-MUC-008',
    'SKU009':'VO-VO-009','SKU010':'VO-VO-010','SKU011':'VO-SO-011','SKU012':'VO-VO-012',
    'SKU013':'VO-GIAY-013','SKU014':'VO-SO-014','SKU015':'VO-VO-015','SKU016':'VO-SO-016',
    'SKU017':'DC-HT-017','SKU018':'DC-VE-018','SKU019':'DC-HT-019','SKU020':'DC-VE-020',
    'SKU021':'DC-HT-021','SKU022':'DC-HT-022','SKU023':'DC-VE-023','SKU024':'DC-VE-024',
    'SKU025':'BL-BL-025','SKU026':'BL-BL-026','SKU027':'BL-TUI-027','SKU028':'BL-TUI-028',
    'SKU029':'BL-BL-029','SKU030':'BL-BL-030','SKU031':'BL-TUI-031','SKU032':'BL-BL-032',
    'SKU033':'MT-MAU-033','SKU034':'MT-MAU-034','SKU035':'MT-MAU-035','SKU036':'MT-DCMT-036',
    'SKU037':'MT-MAU-037','SKU038':'MT-DCMT-038','SKU039':'MT-DCMT-039','SKU040':'MT-DCMT-040',
}

KICH_THUOC = {
    'SKU001':'14 x 0.7 x 0.7 cm','SKU002':'17 x 0.8 x 0.8 cm','SKU003':'12 x 8 x 2 cm','SKU004':'14 x 0.5 x 0.5 cm',
    'SKU005':'10 x 3 x 3 cm','SKU006':'13 x 0.5 x 0.5 cm','SKU007':'15 x 1 x 1 cm','SKU008':'16 x 2 x 2 cm',
    'SKU009':'21 x 29.7 x 0.5 cm','SKU010':'14.8 x 21 x 0.3 cm','SKU011':'14.8 x 21 x 1 cm','SKU012':'21 x 29.7 x 0.3 cm',
    'SKU013':'21 x 29.7 x 1 cm','SKU014':'14.8 x 21 x 0.8 cm','SKU015':'14.8 x 21 x 0.3 cm','SKU016':'10.5 x 14.8 x 0.8 cm',
    'SKU017':'20 x 10 x 8 cm','SKU018':'20 x 3 x 0.2 cm','SKU019':'8 x 5 x 5 cm','SKU020':'18 x 8 x 2 cm',
    'SKU021':'16 x 6 x 1 cm','SKU022':'10 x 2 x 2 cm','SKU023':'30 x 20 x 0.5 cm','SKU024':'15 x 5 x 1 cm',
    'SKU025':'30 x 40 x 12 cm','SKU026':'32 x 42 x 12 cm','SKU027':'25 x 30 x 5 cm','SKU028':'20 x 25 x 8 cm',
    'SKU029':'30 x 40 x 12 cm','SKU030':'25 x 35 x 3 cm','SKU031':'25 x 20 x 10 cm','SKU032':'35 x 45 x 15 cm',
    'SKU033':'10 x 5 x 2 cm','SKU034':'15 x 10 x 3 cm','SKU035':'18 x 8 x 2 cm','SKU036':'15 x 10 x 5 cm',
    'SKU037':'21 x 29.7 x 0.5 cm','SKU038':'20 x 8 x 2 cm','SKU039':'20 x 15 x 0.5 cm','SKU040':'25 x 15 x 10 cm',
}

TU_KHOA = {
    'SKU001':'bút bi gel, bút viết học sinh, bút bi 0.5mm','SKU002':'bút chì gỗ, bút chì HB, bút chì học sinh',
    'SKU003':'bút highlight, bút đánh dấu, bút highlight 5 màu','SKU004':'bút lông kim, bút viết nghệ thuật, bút lông kim 0.4mm',
    'SKU005':'bút xoá nước, bút xoá, dung dịch xoá','SKU006':'ruột bút gel, ruột bút 0.5mm, ruột bút gel',
    'SKU007':'bút chì bấm, bút chì 0.7mm, bút chì bấm học sinh','SKU008':'bút máy luyện chữ, bút máy, bút luyện chữ',
    'SKU009':'vở kẻ ngang, vở 200 trang, vở học sinh','SKU010':'vở ô ly, vở ô ly lớp 1, vở học sinh lớp 1',
    'SKU011':'sổ tay bìa cứng, sổ A5, sổ tay học sinh','SKU012':'vở vẽ A4, vở vẽ không dòng, vở vẽ học sinh',
    'SKU013':'giấy kiểm tra, giấy A4, giấy kiểm tra 100 tờ','SKU014':'sổ lò xo, sổ A5 kẻ ngang, sổ lò xo học sinh',
    'SKU015':'vở tập viết, vở lớp 2, vở tập viết học sinh','SKU016':'sổ ghi chép, sổ A6, sổ ghi chép học sinh',
    'SKU017':'hộp bút, hộp bút vải, hộp bút 2 khoá','SKU018':'thước kẻ, thước kẻ nhựa, thước 20cm',
    'SKU019':'gọt bút chì, gọt bút chì có hộp, gọt bút học sinh','SKU020':'bộ compa, compa 8 chi tiết, compa học sinh',
    'SKU021':'kéo học sinh, kéo mũi tròn, kéo học sinh an toàn','SKU022':'băng keo trong, băng keo 12mm, băng keo học sinh',
    'SKU023':'bảng con, bảng con 2 mặt, bảng con học sinh','SKU024':'ê ke, ê ke 2 chiếc, ê ke học sinh',
    'SKU025':'ba lô chống gù, ba lô lớp 1, ba lô chống gù lớp 1-3','SKU026':'ba lô chống gù, ba lô lớp 4, ba lô chống gù lớp 4-5',
    'SKU027':'túi đeo chéo, túi đeo chéo A4, túi học sinh','SKU028':'túi đựng bình nước, túi bình nước, túi học sinh',
    'SKU029':'ba lô phản quang, ba lô có phản quang, ba lô học sinh','SKU030':'cặp chống nước, cặp lớp 1, cặp chống nước học sinh',
    'SKU031':'túi giữ nhiệt, túi giữ nhiệt hộp cơm, túi học sinh','SKU032':'balo thể thao, balo trẻ em, balo học sinh',
    'SKU033':'sáp màu, sáp màu 24 màu, sáp màu học sinh','SKU034':'màu nước, màu nước 12 ô, màu nước học sinh',
    'SKU035':'bút chì màu, bút chì màu 36 màu, bút chì màu học sinh','SKU036':'đất sét nặn, đất sét 12 màu, đất sét học sinh',
    'SKU037':'giấy màu, giấy màu thủ công, giấy màu học sinh','SKU038':'cọ vẽ, cọ vẽ 6 chiếc, cọ vẽ học sinh',
    'SKU039':'bảng pha màu, bảng pha màu nhựa, bảng pha màu học sinh','SKU040':'bộ tô tượng, tô tượng thạch cao, tô tượng học sinh',
}

MO_TA_NGAN = {
    'SKU001':'Bút bi gel 0.5mm mực đều, viết mượt, phù hợp học sinh tiểu học.',
    'SKU002':'Bút chì gỗ HB đầu nhọn, dễ tẩy, phù hợp học sinh cấp 1.',
    'SKU003':'Bút highlight 5 màu sắc nổi bật, đánh dấu tài liệu hiệu quả.',
    'SKU004':'Bút lông kim 0.4mm vẽ nét mảnh, lý tưởng cho họa sĩ chữ và nghệ thuật.',
    'SKU005':'Bút xoá nước 8ml xoá sạch, không để lại vết, tiện lợi cho học sinh.',
    'SKU006':'Ruột bút gel 0.5mm thay thế, mực đều, viết êm, tiết kiệm chi phí.',
    'SKU007':'Bút chì bấm 0.7mm tiện lợi, không cần gọt, phù hợp học sinh.',
    'SKU008':'Bút máy luyện chữ giúp cải thiện chữ viết, phù hợp học sinh tiểu học.',
    'SKU009':'Vở kẻ ngang 200 trang giấy dày, phù hợp học sinh ghi chép và tập viết.',
    'SKU010':'Vở ô ly lớp 1 giấy mịn, phù hợp học sinh lớp 1 tập viết.',
    'SKU011':'Sổ tay bìa cứng A5 chắc chắn, phù hợp ghi chép hàng ngày.',
    'SKU012':'Vở vẽ A4 không dòng giấy dày, lý tưởng cho học sinh vẽ và sáng tạo.',
    'SKU013':'Giấy kiểm tra A4 100 tờ giấy trắng mịn, phù hợp in đề kiểm tra.',
    'SKU014':'Sổ lò xo A5 kẻ ngang tiện lỡ, phù hợp học sinh ghi chép.',
    'SKU015':'Vở tập viết lớp 2 giấy mịn, phù hợp học sinh lớp 2 tập viết.',
    'SKU016':'Sổ ghi chép A6 nhỏ gọn, tiện lỡ mang theo, phù hợp ghi chép nhanh.',
    'SKU017':'Hộp bút vải 2 khoá chắc chắn, đựng đầy đủ dụng cụ học tập.',
    'SKU018':'Thước kẻ nhựa 20cm trong suốt, phù hợp học sinh đo đạc và vẽ.',
    'SKU019':'Gọt bút chì có hộp tiện lỡ, giữ bút chì sạch sẽ, phù hợp học sinh.',
    'SKU020':'Bộ compa 8 chi tiết đầy đủ, phù hợp học sinh học hình học.',
    'SKU021':'Kéo học sinh mũi tròn an toàn, phù hợp học sinh tiểu học.',
    'SKU022':'Băng keo trong 12mm dính chắc, phù hợp học sinh dán tài liệu.',
    'SKU023':'Bảng con 2 mặt tiện lỡ, phù hợp học sinh viết và tính toán.',
    'SKU024':'Ê ke bộ 2 chiếc chắc chắn, phù hợp học sinh học hình học.',
    'SKU025':'Ba lô chống gù lớp 1-3 thiết kế êm ái, phù hợp học sinh tiểu học.',
    'SKU026':'Ba lô chống gù lớp 4-5 thiết kế êm ái, phù hợp học sinh cấp 2.',
    'SKU027':'Túi đeo chéo A4 nhẹ nhàng, phù hợp học sinh đựng sách vở.',
    'SKU028':'Túi đựng bình nước tiện lỡ, phù hợp học sinh mang bình nước.',
    'SKU029':'Ba lô có phản quang an toàn, phù hợp học sinh đường đi tối.',
    'SKU030':'Cặp chống nước lớp 1 bền bỉ, phù hợp học sinh lớp 1.',
    'SKU031':'Túi giữ nhiệt hộp cơm giữ nhiệt tốt, phù hợp học sinh mang cơm.',
    'SKU032':'Balo thể thao trẻ em nhẹ nhàng, phù hợp học sinh đi chơi.',
    'SKU033':'Sáp màu 24 màu sắc nổi bật, phù hợp học sinh vẽ và sáng tạo.',
    'SKU034':'Màu nước 12 ô màu sắc đẹp, phù hợp học sinh học màu.',
    'SKU035':'Bút chì màu 36 màu sắc đẹp, phù hợp học sinh vẽ và tô màu.',
    'SKU036':'Đất sét nặn 12 màu sắc nổi bật, phù hợp học sinh nặn hình.',
    'SKU037':'Giấy màu thủ công nhiều màu sắc, phù hợp học sinh làm thủ công.',
    'SKU038':'Cọ vẽ bộ 6 chiếc chất lượng, phù hợp học sinh học vẽ.',
    'SKU039':'Bảng pha màu nhựa tiện lỡ, phù hợp học sinh pha màu.',
    'SKU040':'Bộ tô tượng thạch cao sáng tạo, phù hợp học sinh học tô tượng.',
}

MO_TA_DAI = {
    'SKU001':'Bút bi gel 0.5mm với mực đều màu, viết mượt mà không bị đặc giấy. Thiết kế nhỏ gọn, cầm nắm thoải mái, phù hợp cho học sinh tiểu học trong việc ghi chép và tập viết hàng ngày. Sản phẩm chính hãng, có hóa đơn đầy đủ.',
    'SKU002':'Bút chì gỗ HB với đầu nhọn chuẩn, chì mềm dễ tẩy sạch. Thân bút làm từ gỗ tự nhiên, thân thiện với môi trường. Phù hợp cho học sinh cấp 1 trong việc tập viết, tập tô và các bài tập hình học.',
    'SKU003':'Bút highlight 5 màu với sắc màu nổi bật: vàng, hồng, xanh lá, xanh dương, tím. Đầu bút chữ V giúp đánh dấu tài liệu hiệu quả, mực khô nhanh không lem. Phù hợp cho học sinh đánh dấu ý quan trọng trong sách giáo khoa.',
    'SKU004':'Bút lông kim 0.4mm với đầu bút mảnh, tạo nét vẽ chính xác và mượt mà. Mực đen đậm, viết êm trên nhiều loại giấy. Lựa chọn lý tưởng cho họa sĩ chữ, học sinh học nghệ thuật và những ai yêu thích vẽ tay.',
    'SKU005':'Bút xoá nước 8ml với dung dịch xoá mịn, xoá sạch không để lại vết ố vàng. Thiết kế nhỏ gọn, dễ dàng mang theo trong hộp bút. Phù hợp cho học sinh khi cần chỉnh sửa bài viết nhanh chóng.',
    'SKU006':'Ruột bút gel 0.5mm thay thế với mực đều màu, viết mượt mà. Thiết kế tiêu chuẩn, tương thích với nhiều loại bút bi. Giúp tiết kiệm chi phí khi cần thay thế ruột bút.',
    'SKU007':'Bút chì bấm 0.7mm với cơ cấu bấm tiện lợi, không cần gọt bút. Chì mềm, dễ tẩy, phù hợp cho học sinh trong các bài kiểm tra và tập viết hàng ngày. Thiết kế nhẹ nhàng, cầm nắm thoải mái.',
    'SKU008':'Bút máy luyện chữ với thiết kế đặc biệt giúp cải thiện chữ viết. Mực đen đậm, viết mượt mà trên nhiều loại giấy. Phù hợp cho học sinh tiểu học trong giai đoạn luyện chữ viết đẹp.',
    'SKU009':'Vở kẻ ngang 200 trang với giấy dày mịn, không loang mực. Bìa vở đẹp, chắc chắn, bảo vệ tốt các trang bên trong. Phù hợp cho học sinh ghi chép, tập viết và làm bài tập hàng ngày.',
    'SKU010':'Vở ô ly lớp 1 với các ô ly chuẩn, giúp học sinh lớp 1 dễ dàng làm quen với cách viết. Giấy mịn, không loang mực, phù hợp với bút chì và bút mực.',
    'SKU011':'Sổ tay bìa cứng A5 với bìa chắc chắn, bảo vệ tốt các trang bên trong. Giấy mịn, phù hợp ghi chép hàng ngày, ghi chú bài học và các ý tưởng cá nhân.',
    'SKU012':'Vở vẽ A4 không dòng với giấy dày, phù hợp cho học sinh vẽ và sáng tạo. Bề mặt giấy mịn, dễ tô màu, không loang mực. Lựa chọn lý tưởng cho các bài tập vẽ trong trường học.',
    'SKU013':'Giấy kiểm tra A4 100 tờ với giấy trắng mịn, phù hợp in đề kiểm tra và các tài liệu quan trọng. Độ dày chuẩn, không loang mực khi in ấn.',
    'SKU014':'Sổ lò xo A5 kẻ ngang với thiết kế tiện lợi, dễ dàng mở ra và lật trang. Giấy mịn, phù hợp học sinh ghi chép và làm bài tập.',
    'SKU015':'Vở tập viết lớp 2 với giấy mịn, phù hợp học sinh lớp 2 trong việc tập viết và làm bài. Thiết kế đơn giản, dễ sử dụng.',
    'SKU016':'Sổ ghi chép A6 nhỏ gọn, tiện lợi mang theo mọi lúc mọi nơi. Giấy mịn, phù hợp ghi chép nhanh, ghi chú quan trọng.',
    'SKU017':'Hộp bút vải 2 khoá với chất liệu vải bền bỉ, chống nước. Thiết kế 2 khoá tiện lợi, đựng đầy đủ dụng cụ học tập. Phù hợp cho học sinh tiểu học.',
    'SKU018':'Thước kẻ nhựa 20cm trong suốt, có vạch chia chính xác. Phù hợp cho học sinh trong việc đo đạc, vẽ hình học và các bài tập toán.',
    'SKU019':'Gọt bút chì có hộp với thiết kế tiện lợi, giữ cho bút chì luôn sạch sẽ. Phù hợp cho học sinh trong việc gọt bút chì nhanh chóng.',
    'SKU020':'Bộ compa 8 chi tiết đầy đủ với các dụng cụ chính xác. Phù hợp cho học sinh trong việc học hình học, vẽ các hình khối và các bài tập toán.',
    'SKU021':'Kéo học sinh mũi tròn với thiết kế an toàn, phù hợp cho học sinh tiểu học. Lưỡi kéo sắc bén, dễ dàng cắt giấy và các vật liệu mỏng.',
    'SKU022':'Băng keo trong 12mm với chất lượng dính tốt, không để lại vết. Phù hợp cho học sinh trong việc dán tài liệu, làm thủ công.',
    'SKU023':'Bảng con 2 mặt với thiết kế tiện lợi, phù hợp cho học sinh trong việc viết, tính toán và ghi chú. Dễ dàng lau chùi và sử dụng lại.',
    'SKU024':'Ê ke bộ 2 chiếc với chất liệu chắc chắn, phù hợp cho học sinh trong việc học hình học. Giúp vẽ các đường thẳng và góc chuẩn xác.',
    'SKU025':'Ba lô chống gù lớp 1-3 với thiết kế êm ái, giảm áp lực lên vai khi mang nhiều sách vở. Chất liệu bền bỉ, chống nước, phù hợp cho học sinh tiểu học.',
    'SKU026':'Ba lô chống gù lớp 4-5 với thiết kế êm ái, giảm áp lực lên vai khi mang nhiều sách vở. Chất liệu bền bỉ, chống nước, phù hợp cho học sinh cấp 2.',
    'SKU027':'Túi đeo chéo A4 với thiết kế nhẹ nhàng, phù hợp cho học sinh đựng sách vở và tài liệu. Chất liệu bền bỉ, dễ dàng vệ sinh.',
    'SKU028':'Túi đựng bình nước với thiết kế tiện lợi, phù hợp cho học sinh mang bình nước đến trường. Chất liệu bền bỉ, giữ nhiệt tốt.',
    'SKU029':'Ba lô có phản quang với thiết kế an toàn, giúp học sinh được nhìn rõ vào ban đêm. Chất liệu bền bỉ, phù hợp cho học sinh đường đi tối.',
    'SKU030':'Cặp chống nước lớp 1 với thiết kế bền bỉ, bảo vệ tốt sách vở khỏi nước. Phù hợp cho học sinh lớp 1 trong mọi điều kiện thời tiết.',
    'SKU031':'Túi giữ nhiệt hộp cơm với khả năng giữ nhiệt tốt, giữ cho thức ăn luôn ấm. Phù hợp cho học sinh mang cơm đến trường.',
    'SKU032':'Balo thể thao trẻ em với thiết kế nhẹ nhàng, phù hợp cho học sinh đi chơi và vận động. Chất liệu bền bỉ, nhiều ngăn tiện lợi.',
    'SKU033':'Sáp màu 24 màu với sắc màu nổi bật, phù hợp cho học sinh trong việc vẽ và sáng tạo. Chất liệu mềm, dễ dàng tô màu.',
    'SKU034':'Màu nước 12 ô với sắc màu đẹp, phù hợp cho học sinh trong việc học màu và vẽ. Chất liệu cao cấp, không độc hại.',
    'SKU035':'Bút chì màu 36 màu với sắc màu đẹp, phù hợp cho học sinh trong việc vẽ và tô màu. Chì mềm, dễ dàng tô màu.',
    'SKU036':'Đất sét nặn 12 màu với sắc màu nổi bật, phù hợp cho học sinh trong việc nặn hình và sáng tạo. Chất liệu mềm, dễ dàng nặn hình.',
    'SKU037':'Giấy màu thủ công với nhiều màu sắc, phù hợp cho học sinh trong việc làm thủ công và sáng tạo. Chất liệu dày, dễ dàng cắt và dán.',
    'SKU038':'Cọ vẽ bộ 6 chiếc với chất lượng cao, phù hợp cho học sinh trong việc học vẽ. Lông cọ mềm, dễ dàng tô màu.',
    'SKU039':'Bảng pha màu nhựa với thiết kế tiện lợi, phù hợp cho học sinh trong việc pha màu. Chất liệu bền bỉ, dễ dàng vệ sinh.',
    'SKU040':'Bộ tô tượng thạch cao với thiết kế sáng tạo, phù hợp cho học sinh trong việc học tô tượng. Chất liệu cao cấp, dễ dàng sử dụng.',
}

DANH_MUC_CHA = {
    'SKU001':'Bút viết > Bút bi & ruột bút','SKU002':'Bút viết > Bút chì & mực','SKU003':'Bút viết > Bút chì & mực',
    'SKU004':'Bút viết > Bút chì & mực','SKU005':'Bút viết > Bút chì & mực','SKU006':'Bút viết > Bút bi & ruột bút',
    'SKU007':'Bút viết > Bút chì & mực','SKU008':'Bút viết > Bút chì & mực',
    'SKU009':'Vở & sổ > Vở','SKU010':'Vở & sổ > Vở','SKU011':'Vở & sổ > Sổ & giấy','SKU012':'Vở & sổ > Vở',
    'SKU013':'Vở & sổ > Sổ & giấy','SKU014':'Vở & sổ > Sổ & giấy','SKU015':'Vở & sổ > Vở','SKU016':'Vở & sổ > Sổ & giấy',
    'SKU017':'Dụng cụ > Dụng cụ học tập','SKU018':'Dụng cụ > Dụng cụ vẽ','SKU019':'Dụng cụ > Dụng cụ học tập',
    'SKU020':'Dụng cụ > Dụng cụ vẽ','SKU021':'Dụng cụ > Dụng cụ học tập','SKU022':'Dụng cụ > Dụng cụ học tập',
    'SKU023':'Dụng cụ > Dụng cụ vẽ','SKU024':'Dụng cụ > Dụng cụ vẽ',
    'SKU025':'Ba lô & túi > Ba lô','SKU026':'Ba lô & túi > Ba lô','SKU027':'Ba lô & túi > Túi','SKU028':'Ba lô & túi > Túi',
    'SKU029':'Ba lô & túi > Ba lô','SKU030':'Ba lô & túi > Ba lô','SKU031':'Ba lô & túi > Túi','SKU032':'Ba lô & túi > Ba lô',
    'SKU033':'Mỹ thuật > Màu vẽ','SKU034':'Mỹ thuật > Màu vẽ','SKU035':'Mỹ thuật > Màu vẽ','SKU036':'Mỹ thuật > Dụng cụ mỹ thuật',
    'SKU037':'Mỹ thuật > Màu vẽ','SKU038':'Mỹ thuật > Dụng cụ mỹ thuật','SKU039':'Mỹ thuật > Dụng cụ mỹ thuật','SKU040':'Mỹ thuật > Dụng cụ mỹ thuật',
}

KHOI_LOP = {
    'SKU001':'Mọi lớp','SKU002':'Mọi lớp','SKU003':'Mọi lớp','SKU004':'Mọi lớp','SKU005':'Mọi lớp','SKU006':'Mọi lớp',
    'SKU007':'Mọi lớp','SKU008':'Mọi lớp','SKU009':'Mọi lớp','SKU010':'Lớp 1','SKU011':'Mọi lớp','SKU012':'Mọi lớp',
    'SKU013':'Mọi lớp','SKU014':'Mọi lớp','SKU015':'Lớp 2','SKU016':'Mọi lớp','SKU017':'Mọi lớp','SKU018':'Mọi lớp',
    'SKU019':'Mọi lớp','SKU020':'Mọi lớp','SKU021':'Mọi lớp','SKU022':'Mọi lớp','SKU023':'Mọi lớp','SKU024':'Mọi lớp',
    'SKU025':'Lớp 1-3','SKU026':'Lớp 4-5','SKU027':'Mọi lớp','SKU028':'Mọi lớp','SKU029':'Mọi lớp','SKU030':'Lớp 1',
    'SKU031':'Mọi lớp','SKU032':'Mọi lớp','SKU033':'Mọi lớp','SKU034':'Mọi lớp','SKU035':'Mọi lớp','SKU036':'Mọi lớp',
    'SKU037':'Mọi lớp','SKU038':'Mọi lớp','SKU039':'Mọi lớp','SKU040':'Mọi lớp',
}

LOAI_SP = {
    'SKU001':'Bút bi','SKU002':'Bút chì','SKU003':'Bút highlight','SKU004':'Bút lông kim','SKU005':'Bút xoá',
    'SKU006':'Ruột bút','SKU007':'Bút chì bấm','SKU008':'Bút máy','SKU009':'Vở','SKU010':'Vở','SKU011':'Sổ',
    'SKU012':'Vở','SKU013':'Giấy','SKU014':'Sổ','SKU015':'Vở','SKU016':'Sổ','SKU017':'Hộp bút','SKU018':'Thước kẻ',
    'SKU019':'Gọt bút chì','SKU020':'Compa','SKU021':'Kéo','SKU022':'Băng keo','SKU023':'Bảng con','SKU024':'Ê ke',
    'SKU025':'Ba lô','SKU026':'Ba lô','SKU027':'Túi','SKU028':'Túi','SKU029':'Ba lô','SKU030':'Cặp','SKU031':'Túi',
    'SKU032':'Balo','SKU033':'Sáp màu','SKU034':'Màu nước','SKU035':'Bút chì màu','SKU036':'Đất sét','SKU037':'Giấy màu',
    'SKU038':'Cọ vẽ','SKU039':'Bảng pha màu','SKU040':'Tô tượng',
}

def main():
    with open(os.path.join(DS, 'products.csv'), encoding='utf-8-sig') as f:
        rows = list(csv.DictReader(f))

    new_rows = []
    for r in rows:
        old = r['sku']
        new = OLD_SKU[old]
        new_rows.append({
            'sku_cu': old,
            'sku': new,
            'ten_san_pham': r['ten_san_pham'],
            'danh_muc': DANH_MUC_CHA[old],
            'gia_ban': r['gia_ban'],
            'gia_von': r['gia_von'],
            'ton_kho': r['ton_kho'],
            'trong_luong_g': r['trong_luong_g'],
            'kiem_dinh_an_toan': r['kiem_dinh_an_toan'],
            'kich_thuoc': KICH_THUOC[old],
            'tu_khoa': TU_KHOA[old],
            'mo_ta_ngan': MO_TA_NGAN[old],
            'mo_ta_dai': MO_TA_DAI[old],
            'khoi_lop': KHOI_LOP[old],
            'loai_san_pham': LOAI_SP[old],
        })

    cols = ['sku_cu','sku','ten_san_pham','danh_muc','gia_ban','gia_von','ton_kho',
            'trong_luong_g','kiem_dinh_an_toan','kich_thuoc','tu_khoa',
            'mo_ta_ngan','mo_ta_dai','khoi_lop','loai_san_pham']
    with open(os.path.join(DS, 'products.csv'), 'w', encoding='utf-8-sig', newline='') as f:
        w = csv.DictWriter(f, fieldnames=cols, quoting=csv.QUOTE_ALL)
        w.writeheader()
        w.writerows(new_rows)
    print(f'products.csv: {len(new_rows)} rows, {len(cols)} cols')

    wc_cols = ['Type','SKU','Name','Short description','Description','Categories','Images',
               'Stock','Regular price','Meta: gia_von','Weight (kg)','Length (cm)','Width (cm)','Height (cm)',
               'Attribute 1 name','Attribute 1 value(s)','Attribute 1 visible','Attribute 1 global',
               'Attribute 2 name','Attribute 2 value(s)','Attribute 2 visible','Attribute 2 global',
               'Attribute 3 name','Attribute 3 value(s)','Attribute 3 visible','Attribute 3 global']
    wc_rows = []
    for r in new_rows:
        wt = round(int(r['trong_luong_g'])/1000, 3)
        dims = r['kich_thuoc'].replace(' ','').split('x')
        wc_rows.append({
            'Type':'simple','SKU':r['sku'],'Name':r['ten_san_pham'],
            'Short description':r['mo_ta_ngan'],'Description':r['mo_ta_dai'],
            'Categories':r['danh_muc'],'Images':f"img/{r['sku'].lower()}.webp",
            'Stock':r['ton_kho'],'Regular price':r['gia_ban'],'Meta: gia_von':r['gia_von'],
            'Weight (kg)':wt,'Length (cm)':dims[0],'Width (cm)':dims[1],'Height (cm)':dims[2],
            'Attribute 1 name':'Kiểm định an toàn','Attribute 1 value(s)':r['kiem_dinh_an_toan'],
            'Attribute 1 visible':'1','Attribute 1 global':'1',
            'Attribute 2 name':'Khối lớp','Attribute 2 value(s)':r['khoi_lop'],
            'Attribute 2 visible':'1','Attribute 2 global':'1',
            'Attribute 3 name':'Loại sản phẩm','Attribute 3 value(s)':r['loai_san_pham'],
            'Attribute 3 visible':'1','Attribute 3 global':'1',
        })
    with open(os.path.join(DS, 'woocommerce-import.csv'), 'w', encoding='utf-8-sig', newline='') as f:
        w = csv.DictWriter(f, fieldnames=wc_cols, quoting=csv.QUOTE_ALL)
        w.writeheader()
        w.writerows(wc_rows)
    print(f'woocommerce-import.csv: {len(wc_rows)} rows, {len(wc_cols)} cols')

if __name__ == '__main__':
    main()
