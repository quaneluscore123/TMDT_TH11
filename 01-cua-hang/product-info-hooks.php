<?php
add_action('woocommerce_single_product_summary', 'custom_product_info', 25);
function custom_product_info() {
    echo '<div class="custom-product-info" style="margin:20px 0;padding:15px;background:#f7f7f7;border-left:4px solid #7f54b3;">';
    echo '<p><strong>Thời gian giao hàng:</strong> 2-4 ngày làm việc trong nội thành Hà Nội; 3-7 ngày cho các tỉnh khác.</p>';
    echo '<p><strong>Chính sách đổi trả:</strong> Đổi trả trong 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả. Liên hệ: 0901 234 567 (Zalo/Viber).</p>';
    echo '</div>';
}
