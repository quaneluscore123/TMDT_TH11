<?php
/**
 * Plugin Name: Nhom25 - UX/UI Upgrade Pack
 * Description: Bộ nâng cấp toàn diện UX/UI cho Bút Chì Xanh: Sticky Header, Live Search gợi ý sản phẩm, Mini-Cart Drawer với thanh tiến độ Freeship 499k, nút Mua Ngay (Buy Now), Quick View xem nhanh, Trust Badges theo dữ liệu kiểm định thật.
 * Author: Nhom 25 (Bút Chì Xanh)
 * Version: 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// 1. BUY NOW (MUA NGAY) & TRUST BADGES TRÊN TRANG CHI TIẾT SẢN PHẨM (CONCEPT 3)
// =============================================================================

// Thêm nút "MUA NGAY" cạnh nút Thêm vào giỏ
add_action('woocommerce_after_add_to_cart_button', function () {
    global $product;
    if (!$product) return;
    $pid = $product->get_id();
    echo '<button type="submit" name="n25_buy_now" value="' . esc_attr($pid) . '" data-product-id="' . esc_attr($pid) . '" class="button n25-btn-buy-now">'
       . '<span class="n25-btn-icon">⚡</span> MUA NGAY'
       . '</button>';
}, 20);

// Xử lý server-side: Nếu submit qua n25_buy_now, tự động thêm vào giỏ và chuyển hướng thẳng sang Checkout
add_action('template_redirect', function () {
    if (isset($_REQUEST['n25_buy_now']) && !empty($_REQUEST['n25_buy_now'])) {
        $product_id = absint($_REQUEST['n25_buy_now']);
        $quantity = isset($_REQUEST['quantity']) ? wc_stock_amount(wp_unslash($_REQUEST['quantity'])) : 1;
        if ($product_id > 0 && function_exists('WC') && WC()->cart) {
            // Thêm vào giỏ hàng nếu chưa thêm qua cơ chế mặc định
            $found = false;
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                WC()->cart->add_to_cart($product_id, $quantity);
            }
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
    }
}, 5);

// Filter chuyển hướng WooCommerce mặc định sang checkout nếu n25_buy_now được truyền
add_filter('woocommerce_add_to_cart_redirect', function ($url) {
    if (isset($_REQUEST['n25_buy_now'])) {
        return wc_get_checkout_url();
    }
    return $url;
});

// Thêm khối Trust Badges uy tín dưới nút giỏ hàng trên trang sản phẩm
add_action('woocommerce_single_product_summary', function () {
    global $product;
    $da_kiem_dinh = $product && has_term('co', 'pa_kiem-dinh-an-toan', $product->get_id());
    ?>
    <div class="n25-trust-box">
        <div class="n25-trust-box__title">
            <span class="n25-trust-shield">🛡️</span> YÊN TÂM MUA SẮM TẠI BÚT CHÌ XANH
        </div>
        <div class="n25-trust-box__grid">
            <div class="n25-trust-item">
                <span class="n25-trust-item__icon">✓</span>
                <div>
                    <?php if ($da_kiem_dinh): ?>
                    <b>Đã kiểm định an toàn</b>
                    <span>Sản phẩm này có kết quả kiểm định an toàn do nhà cung cấp cung cấp</span>
                    <?php else: ?>
                    <b>Thông tin kiểm định minh bạch</b>
                    <span>Sản phẩm này chưa có giấy kiểm định; xem mục Kiểm định an toàn ở bộ lọc để chọn hàng đã kiểm định</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="n25-trust-item">
                <span class="n25-trust-item__icon">🚚</span>
                <div>
                    <b>Giao nhanh toàn quốc</b>
                    <span>Miễn phí vận chuyển cho đơn hàng từ 499.000đ</span>
                </div>
            </div>
            <div class="n25-trust-item">
                <span class="n25-trust-item__icon">🔄</span>
                <div>
                    <b>Đổi trả dễ dàng 7 ngày</b>
                    <span>Đổi mới tận nhà nếu sản phẩm có lỗi từ nhà sản xuất</span>
                </div>
            </div>
            <div class="n25-trust-item">
                <span class="n25-trust-item__icon">🔒</span>
                <div>
                    <b>Bảo mật & Tuân thủ pháp luật</b>
                    <span>Bảo vệ quyền riêng tư theo Nghị định 13/2023/NĐ-CP</span>
                </div>
            </div>
        </div>
    </div>
    <?php
}, 35);

// =============================================================================
// 2. LIVE SEARCH AJAX ENDPOINT (CONCEPT 1)
// =============================================================================
add_action('wp_ajax_n25_live_search', 'n25_handle_live_search');
add_action('wp_ajax_nopriv_n25_live_search', 'n25_handle_live_search');

function n25_handle_live_search() {
    $keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';
    if (empty($keyword) || mb_strlen($keyword) < 2) {
        wp_send_json_success(['results' => []]);
    }

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        's'              => $keyword,
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            if ($product) {
                $results[] = [
                    'id'    => $product->get_id(),
                    'title' => $product->get_name(),
                    'url'   => $product->get_permalink(),
                    'price' => $product->get_price_html(),
                    'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                    'sku'   => $product->get_sku(),
                ];
            }
        }
        wp_reset_postdata();
    }

    wp_send_json_success(['results' => $results]);
}

// =============================================================================
// 3. MINI-CART DRAWER & FREESHIP PROGRESS BAR (CONCEPT 2)
// =============================================================================

// Ajax lấy nội dung giỏ hàng cho drawer
add_action('wp_ajax_n25_get_cart_drawer', 'n25_render_cart_drawer_content');
add_action('wp_ajax_nopriv_n25_get_cart_drawer', 'n25_render_cart_drawer_content');

function n25_get_freeship_threshold() {
    return 499000; // Ngưỡng 499.000đ
}

function n25_render_cart_drawer_content() {
    if (!WC()->cart) {
        wp_send_json_error();
    }

    $subtotal = WC()->cart->get_subtotal();
    $threshold = n25_get_freeship_threshold();
    $diff = $threshold - $subtotal;
    $percent = min(100, max(0, round(($subtotal / $threshold) * 100)));

    ob_start();
    ?>
    <div class="n25-drawer__freeship">
        <?php if ($diff > 0): ?>
            <div class="n25-drawer__freeship-text">
                🎁 Mua thêm <b><?php echo wc_price($diff); ?></b> để được <span>MIỄN PHÍ VẬN CHUYỂN</span>!
            </div>
        <?php else: ?>
            <div class="n25-drawer__freeship-text n25-drawer__freeship-text--qualified">
                🎉 Tuyệt vời! Đơn hàng của bạn đã đủ điều kiện <b>MIỄN PHÍ VẬN CHUYỂN</b>!
            </div>
        <?php endif; ?>
        <div class="n25-drawer__progress-bar">
            <div class="n25-drawer__progress-fill" style="width: <?php echo esc_attr($percent); ?>%;"></div>
        </div>
    </div>

    <div class="n25-drawer__items">
        <?php if (WC()->cart->is_empty()): ?>
            <div class="n25-drawer__empty">
                <span class="n25-drawer__empty-icon">🛒</span>
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="button n25-drawer__btn-shop">Khám phá sản phẩm ngay</a>
            </div>
        <?php else: ?>
            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item): 
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) continue;
            ?>
                <div class="n25-cart-item">
                    <div class="n25-cart-item__thumb">
                        <?php echo $_product->get_image('thumbnail'); ?>
                    </div>
                    <div class="n25-cart-item__details">
                        <a href="<?php echo esc_url($_product->get_permalink()); ?>" class="n25-cart-item__title">
                            <?php echo esc_html($_product->get_name()); ?>
                        </a>
                        <div class="n25-cart-item__meta">
                            <span class="n25-cart-item__price"><?php echo wc_price($_product->get_price()); ?></span>
                            <span class="n25-cart-item__qty">× <?php echo esc_html($cart_item['quantity']); ?></span>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" class="n25-cart-item__remove" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" title="Xóa">✕</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!WC()->cart->is_empty()): ?>
        <div class="n25-drawer__footer">
            <div class="n25-drawer__subtotal">
                <span>Tạm tính:</span>
                <b><?php wc_cart_totals_subtotal_html(); ?></b>
            </div>
            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="button n25-drawer__btn-checkout">
                🔒 TIẾN HÀNH THANH TOÁN
            </a>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="n25-drawer__link-cart">
                Xem lại giỏ hàng đầy đủ
            </a>
        </div>
    <?php endif; ?>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html, 'count' => WC()->cart->get_cart_contents_count()]);
}

// In cấu trúc Drawer vào cuối trang
add_action('wp_footer', function () {
    ?>
    <!-- Mini-Cart Drawer Container -->
    <div id="n25-cart-drawer" class="n25-drawer" aria-hidden="true">
        <div class="n25-drawer__overlay" id="n25-drawer-overlay"></div>
        <div class="n25-drawer__panel">
            <div class="n25-drawer__header">
                <div class="n25-drawer__title">
                    <span>🛒</span> Giỏ Hàng (<span id="n25-drawer-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>)
                </div>
                <button type="button" class="n25-drawer__close" id="n25-drawer-close" aria-label="Đóng">✕</button>
            </div>
            <div class="n25-drawer__body" id="n25-drawer-body">
                <!-- Nội dung được load qua AJAX -->
            </div>
        </div>
    </div>

    <!-- Quick View Modal Container -->
    <div id="n25-quickview-modal" class="n25-qv-modal" aria-hidden="true">
        <div class="n25-qv-modal__overlay" id="n25-qv-overlay"></div>
        <div class="n25-qv-modal__panel">
            <button type="button" class="n25-qv-modal__close" id="n25-qv-close">✕</button>
            <div class="n25-qv-modal__body" id="n25-qv-body">
                <div class="n25-qv-loading">Đang tải thông tin sản phẩm...</div>
            </div>
        </div>
    </div>
    <?php
});

// Thêm nút Quick View vào vòng lặp thẻ sản phẩm
add_action('woocommerce_after_shop_loop_item', function () {
    global $product;
    if (!$product) return;
    echo '<button type="button" class="n25-btn-quickview" data-product-id="' . esc_attr($product->get_id()) . '">👁️ Xem nhanh</button>';
}, 9);

// AJAX Endpoint cho Quick View Modal
add_action('wp_ajax_n25_get_quickview', 'n25_handle_quickview');
add_action('wp_ajax_nopriv_n25_get_quickview', 'n25_handle_quickview');

function n25_handle_quickview() {
    $pid = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
    if (!$pid) wp_send_json_error('Thiếu mã sản phẩm');

    $product = wc_get_product($pid);
    if (!$product) wp_send_json_error('Sản phẩm không tồn tại');

    ob_start();
    ?>
    <div class="n25-qv-content">
        <div class="n25-qv-gallery">
            <?php echo $product->get_image('large'); ?>
        </div>
        <div class="n25-qv-summary">
            <h2 class="n25-qv-title"><?php echo esc_html($product->get_name()); ?></h2>
            <div class="n25-qv-sku">Mã SKU: <b><?php echo esc_html($product->get_sku()); ?></b></div>
            <?php if ($product->get_review_count() > 0): ?>
            <div class="n25-qv-rating"><?php echo wc_get_rating_html($product->get_average_rating(), $product->get_review_count()); ?> <span>(<?php echo esc_html(number_format_i18n($product->get_average_rating(), 1)); ?>/5 · <?php echo (int) $product->get_review_count(); ?> đánh giá)</span></div>
            <?php endif; ?>
            <?php if (has_term('co', 'pa_kiem-dinh-an-toan', $product->get_id())): ?>
            <div class="n25-qv-rating"><span>✓ Đã kiểm định an toàn</span></div>
            <?php endif; ?>
            <div class="n25-qv-price"><?php echo $product->get_price_html(); ?></div>
            <div class="n25-qv-desc"><?php echo wp_kses_post(wp_trim_words($product->get_short_description() ?: $product->get_description(), 30)); ?></div>
            
            <div class="n25-qv-actions">
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-product_id="<?php echo esc_attr($product->get_id()); ?>" class="button add_to_cart_button ajax_add_to_cart n25-btn-qv-cart">
                    🛒 Thêm vào giỏ hàng
                </a>
                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="n25-qv-link-detail">
                    Xem chi tiết sản phẩm →
                </a>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

// Thêm đánh giá sao uy tín trên thẻ sản phẩm ở trang shop
// Khong gan cung 5 sao cho moi san pham (danh gia gia, trai Luat BVQLNTD 2023).
// WooCommerce/Storefront tu hien so sao khi san pham co danh gia that cua khach.

// =============================================================================
// 4. HERO BANNER TRÊN ĐẦU TRANG CHỦ (CONCEPT 1)
// =============================================================================
add_filter('the_content', function ($content) {
    if (!is_front_page() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $shop_url = wc_get_page_permalink('shop');
    $loc_an_toan = add_query_arg('pa_kiem-dinh-an-toan[]', 'co', $shop_url);
    $term_co = get_term_by('slug', 'co', 'pa_kiem-dinh-an-toan');
    $so_kiem_dinh = $term_co ? (int) $term_co->count : 0;
    $tong_sp = (int) array_sum((array) wp_count_posts('product')->publish);
    $hero_html = '
    <div class="n25-hero-banner">
        <div class="n25-hero-banner__content">
            <div class="n25-hero-tag">🌟 Bút Chì Xanh · Tự Hào Đồng Hành Cùng Trẻ Thơ</div>
            <h1 class="n25-hero-title">MÙA TỰU TRƯỜNG RỰC RỠ 2026</h1>
            <p class="n25-hero-subtitle">Đồ dùng học tập cho học sinh tiểu học, ghi rõ sản phẩm nào đã kiểm định an toàn và hợp với khối lớp nào.</p>
            <div class="n25-hero-badges">
                <span>✓ ' . $so_kiem_dinh . '/' . $tong_sp . ' sản phẩm có kiểm định an toàn</span>
                <span>✓ Miễn phí vận chuyển toàn quốc từ 499.000đ</span>
            </div>
            <div class="n25-hero-cta">
                <a href="' . esc_url($shop_url) . '" class="n25-btn-hero n25-btn-hero--primary">Khám phá cửa hàng →</a>
                <a href="' . esc_url($loc_an_toan) . '" class="n25-btn-hero n25-btn-hero--secondary">Sản phẩm kiểm định</a>
            </div>
        </div>
        <div class="n25-hero-card">
            <div class="n25-hero-card__badge">NĂM HỌC MỚI</div>
            <div class="n25-hero-card__icon">🎒</div>
            <h3>Chọn đồ an toàn cho con</h3>
            <ul>
                <li>• Lọc riêng ' . $so_kiem_dinh . ' sản phẩm đã kiểm định an toàn</li>
                <li>• Lọc theo khối lớp của con</li>
                <li>• Biết trước phí ship 5 vùng, đổi trả 7 ngày</li>
            </ul>
            <div class="n25-hero-card__price">Miễn phí ship từ: <b>499.000đ</b></div>
            <a href="' . esc_url($loc_an_toan) . '" class="n25-hero-card__btn">Xem đồ đã kiểm định</a>
        </div>
    </div>';

    return $hero_html . $content;
}, 5);

// =============================================================================
// 5. CSS & JAVASCRIPT GIAO DIỆN HIỆN ĐẠI
// =============================================================================
add_action('wp_head', function () {
    ?>
    <style id="n25-ux-upgrade-styles">
        /* Brand Colors & Global Design System */
        :root {
            --n25-brand: #059669;
            --n25-brand-dark: #065f46;
            --n25-brand-light: #ecfdf5;
            --n25-accent: #f97316;
            --n25-accent-dark: #ea580c;
            --n25-surface: #ffffff;
            --n25-bg: #f8fafc;
            --n25-border: #e2e8f0;
            --n25-text: #1e293b;
            --n25-muted: #64748b;
        }

        /* 1. STICKY HEADER */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        /* Live Search Bar Styling */
        .site-search {
            position: relative;
        }
        .site-search .widget_product_search input[type=search] {
            border: 2px solid var(--n25-border) !important;
            border-radius: 30px !important;
            padding: 10px 20px 10px 40px !important;
            font-size: 14px;
            background: #f8fafc !important;
            transition: all 0.2s ease;
        }
        .site-search .widget_product_search input[type=search]:focus {
            border-color: var(--n25-brand) !important;
            background: #fff !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        }

        /* Live Search Dropdown */
        .n25-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            border: 1px solid var(--n25-border);
            margin-top: 6px;
            z-index: 1100;
            max-height: 380px;
            overflow-y: auto;
            display: none;
        }
        .n25-search-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none !important;
            transition: background 0.15s;
        }
        .n25-search-item:hover {
            background: var(--n25-brand-light);
        }
        .n25-search-item img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 8px;
        }
        .n25-search-item__info {
            flex: 1;
            min-width: 0;
        }
        .n25-search-item__title {
            font-weight: 600;
            font-size: 13px;
            color: var(--n25-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .n25-search-item__price {
            font-size: 12px;
            color: var(--n25-brand);
            font-weight: 700;
        }

        /* 2. HERO BANNER */
        .n25-hero-banner {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 30px;
            align-items: center;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border: 2px solid #a7f3d0;
            border-radius: 24px;
            padding: 44px 48px;
            margin: 10px 0 35px 0;
            box-shadow: 0 10px 30px rgba(5, 150, 105, 0.08);
        }
        .n25-hero-tag {
            display: inline-block;
            background: #d1fae5;
            color: var(--n25-brand-dark);
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .n25-hero-title {
            font-size: 2.2rem !important;
            font-weight: 800 !important;
            color: var(--n25-brand-dark) !important;
            line-height: 1.25 !important;
            margin: 0 0 12px 0 !important;
        }
        .n25-hero-subtitle {
            font-size: 1.05rem;
            color: #334155;
            line-height: 1.5;
            margin-bottom: 18px;
        }
        .n25-hero-badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--n25-brand-dark);
            margin-bottom: 24px;
        }
        .n25-hero-cta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .n25-btn-hero {
            display: inline-flex;
            align-items: center;
            padding: 12px 26px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none !important;
            transition: all 0.2s;
        }
        .n25-btn-hero--primary {
            background: var(--n25-brand);
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
        }
        .n25-btn-hero--primary:hover {
            background: var(--n25-brand-dark);
            transform: translateY(-2px);
        }
        .n25-btn-hero--secondary {
            background: #fff;
            color: var(--n25-brand) !important;
            border: 2px solid var(--n25-brand);
        }
        .n25-btn-hero--secondary:hover {
            background: var(--n25-brand-light);
        }
        .n25-hero-card {
            background: #fff;
            border-radius: 18px;
            padding: 28px 24px;
            border: 1px solid var(--n25-border);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
            text-align: center;
            position: relative;
        }
        .n25-hero-card__badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: var(--n25-accent);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .n25-hero-card__icon {
            font-size: 40px;
            margin-bottom: 8px;
        }
        .n25-hero-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--n25-text);
            margin-bottom: 12px;
        }
        .n25-hero-card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 16px 0;
            text-align: left;
            font-size: 13px;
            color: var(--n25-muted);
            line-height: 1.6;
        }
        .n25-hero-card__price {
            font-size: 15px;
            color: var(--n25-text);
            margin-bottom: 14px;
        }
        .n25-hero-card__price b {
            font-size: 1.4rem;
            color: var(--n25-accent);
        }
        .n25-hero-card__btn {
            display: block;
            background: var(--n25-brand);
            color: #fff !important;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.2s;
        }
        .n25-hero-card__btn:hover {
            background: var(--n25-brand-dark);
        }

        /* 3. PRODUCT CARDS ENHANCEMENT */
        ul.products li.product {
            border-radius: 18px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04) !important;
            border: 1px solid var(--n25-border) !important;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease !important;
        }
        ul.products li.product:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 16px 32px rgba(5, 150, 105, 0.12) !important;
            border-color: #a7f3d0 !important;
        }
        ul.products li.product img {
            border-radius: 14px !important;
            transition: transform 0.3s ease;
        }
        ul.products li.product:hover img {
            transform: scale(1.02);
        }
        .n25-loop-rating {
            font-size: 12px;
            margin: 4px 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .n25-rating-count {
            color: var(--n25-muted);
            font-weight: 600;
        }
        .n25-btn-quickview {
            width: 100%;
            background: #f1f5f9;
            color: var(--n25-text);
            border: 1px solid var(--n25-border);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .n25-btn-quickview:hover {
            background: var(--n25-brand-light);
            color: var(--n25-brand-dark);
            border-color: var(--n25-brand);
        }

        /* 4. MINI-CART DRAWER */
        .n25-drawer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            visibility: hidden;
            transition: visibility 0.3s ease;
        }
        .n25-drawer.is-active {
            visibility: visible;
        }
        .n25-drawer__overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .n25-drawer.is-active .n25-drawer__overlay {
            opacity: 1;
        }
        .n25-drawer__panel {
            position: absolute;
            top: 0;
            right: 0;
            width: 420px;
            max-width: 90vw;
            height: 100%;
            background: #fff;
            box-shadow: -8px 0 30px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }
        .n25-drawer.is-active .n25-drawer__panel {
            transform: translateX(0);
        }
        .n25-drawer__header {
            padding: 18px 24px;
            background: var(--n25-brand);
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .n25-drawer__title {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .n25-drawer__close {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }
        .n25-drawer__body {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .n25-drawer__freeship {
            background: #ecfdf5;
            padding: 14px 20px;
            border-bottom: 1px solid #a7f3d0;
        }
        .n25-drawer__freeship-text {
            font-size: 13px;
            color: #065f46;
            margin-bottom: 8px;
        }
        .n25-drawer__freeship-text span {
            font-weight: 700;
            color: var(--n25-brand);
        }
        .n25-drawer__freeship-text--qualified {
            color: #047857;
            font-weight: 600;
        }
        .n25-drawer__progress-bar {
            height: 8px;
            background: #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }
        .n25-drawer__progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 6px;
            transition: width 0.4s ease;
        }
        .n25-drawer__items {
            flex: 1;
            padding: 16px 20px;
            overflow-y: auto;
        }
        .n25-cart-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--n25-border);
        }
        .n25-cart-item__thumb img {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--n25-border);
        }
        .n25-cart-item__details {
            flex: 1;
        }
        .n25-cart-item__title {
            font-size: 13px;
            font-weight: 600;
            color: var(--n25-text);
            text-decoration: none !important;
            display: block;
            margin-bottom: 4px;
        }
        .n25-cart-item__meta {
            font-size: 12px;
            color: var(--n25-muted);
        }
        .n25-cart-item__price {
            color: var(--n25-brand);
            font-weight: 700;
            margin-right: 6px;
        }
        .n25-cart-item__remove {
            color: #94a3b8;
            font-size: 14px;
            padding: 6px;
            cursor: pointer;
            text-decoration: none !important;
        }
        .n25-cart-item__remove:hover {
            color: #ef4444;
        }
        .n25-drawer__empty {
            text-align: center;
            padding: 40px 20px;
        }
        .n25-drawer__empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
        }
        .n25-drawer__footer {
            padding: 20px;
            background: #f8fafc;
            border-top: 1px solid var(--n25-border);
        }
        .n25-drawer__subtotal {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            margin-bottom: 14px;
            color: var(--n25-text);
        }
        .n25-drawer__subtotal b {
            font-size: 1.25rem;
            color: var(--n25-brand);
        }
        .n25-drawer__btn-checkout {
            display: block;
            width: 100%;
            background: var(--n25-brand) !important;
            color: #fff !important;
            padding: 14px !important;
            border-radius: 12px !important;
            text-align: center;
            font-size: 15px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            margin-bottom: 10px;
        }
        .n25-drawer__btn-checkout:hover {
            background: var(--n25-brand-dark) !important;
        }
        .n25-drawer__link-cart {
            display: block;
            text-align: center;
            font-size: 13px;
            color: var(--n25-muted);
            text-decoration: none !important;
        }
        .n25-drawer__link-cart:hover {
            color: var(--n25-text);
        }

        /* 5. BUY NOW & TRUST BOX ON SINGLE PRODUCT */
        .n25-btn-buy-now {
            background: var(--n25-accent) !important;
            border-color: var(--n25-accent) !important;
            color: #fff !important;
            padding: 0.9em 2em !important;
            font-size: 1.05em !important;
            border-radius: 10px !important;
            margin-left: 10px !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
            transition: all 0.2s !important;
        }
        .n25-btn-buy-now:hover {
            background: var(--n25-accent-dark) !important;
            border-color: var(--n25-accent-dark) !important;
            transform: translateY(-2px);
        }
        .n25-trust-box {
            background: #f8fafc;
            border: 1px solid var(--n25-border);
            border-radius: 16px;
            padding: 20px 24px;
            margin-top: 24px;
        }
        .n25-trust-box__title {
            font-size: 13px;
            font-weight: 800;
            color: var(--n25-brand-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .n25-trust-box__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 20px;
        }
        .n25-trust-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
        }
        .n25-trust-item__icon {
            font-size: 16px;
            color: var(--n25-brand);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .n25-trust-item b {
            display: block;
            color: var(--n25-text);
            margin-bottom: 2px;
        }
        .n25-trust-item span {
            color: var(--n25-muted);
            font-size: 12px;
            line-height: 1.4;
            display: block;
        }

        /* 6. QUICK VIEW MODAL */
        .n25-qv-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100000;
            visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .n25-qv-modal.is-active {
            visibility: visible;
        }
        .n25-qv-modal__overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .n25-qv-modal.is-active .n25-qv-modal__overlay {
            opacity: 1;
        }
        .n25-qv-modal__panel {
            position: relative;
            background: #fff;
            width: 800px;
            max-width: 100%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            z-index: 10;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .n25-qv-modal.is-active .n25-qv-modal__panel {
            transform: scale(1);
        }
        .n25-qv-modal__close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            z-index: 20;
        }
        .n25-qv-content {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 24px;
            padding: 28px;
        }
        .n25-qv-gallery img {
            border-radius: 14px;
            width: 100%;
            height: auto;
        }
        .n25-qv-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--n25-text);
            margin-bottom: 6px;
        }
        .n25-qv-sku {
            font-size: 12px;
            color: var(--n25-muted);
            margin-bottom: 8px;
        }
        .n25-qv-rating {
            font-size: 13px;
            color: #eab308;
            margin-bottom: 12px;
        }
        .n25-qv-rating span {
            color: var(--n25-muted);
            font-size: 12px;
        }
        .n25-qv-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--n25-brand);
            margin-bottom: 12px;
        }
        .n25-qv-desc {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .n25-qv-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .n25-btn-qv-cart {
            background: var(--n25-brand) !important;
            color: #fff !important;
            padding: 12px !important;
            border-radius: 10px !important;
            text-align: center;
            font-weight: 700 !important;
            text-decoration: none !important;
        }
        .n25-qv-link-detail {
            font-size: 13px;
            color: var(--n25-brand);
            font-weight: 600;
            text-decoration: none !important;
            text-align: center;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .n25-hero-banner {
                grid-template-columns: 1fr;
                padding: 24px 20px;
                gap: 20px;
            }
            .n25-hero-title {
                font-size: 1.6rem !important;
            }
            .n25-trust-box__grid {
                grid-template-columns: 1fr;
            }
            .n25-btn-buy-now {
                margin-left: 0 !important;
                margin-top: 10px !important;
                width: 100%;
            }
            .n25-qv-content {
                grid-template-columns: 1fr;
            }
        }

        /* 7. CAN GIUA MA QR VIETQR VA BANG THONG TIN TREN TRANG THANH TOAN/DON HANG */
        .woocommerce-vietqr-qr-scan,
        .woocommerce-vietqr-bank-details {
            text-align: center !important;
            margin: 25px auto !important;
            max-width: 650px !important;
            float: none !important;
            clear: both !important;
        }

        #qrcode,
        .woocommerce-vietqr-qr-scan #qrcode {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
            margin: 0 auto 20px auto !important;
            width: 100% !important;
            float: none !important;
        }

        #qrcode img,
        .woocommerce-vietqr-qr-scan img {
            display: block !important;
            margin: 0 auto !important;
            max-width: 380px !important;
            width: 100% !important;
            height: auto !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid var(--n25-border) !important;
        }

        .woocommerce-vietqr-bank-details h4 {
            text-align: center !important;
            margin-bottom: 20px !important;
        }

        .woocommerce-vietqr-bank-details table {
            margin: 20px auto !important;
            max-width: 580px !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            border: 1px solid var(--n25-border) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04) !important;
        }
    </style>
    <?php
}, 100);

add_action('wp_footer', function () {
    ?>
    <script id="n25-ux-upgrade-scripts">
    document.addEventListener('DOMContentLoaded', function () {
        const ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

        // 1. MINI-CART DRAWER
        const drawer = document.getElementById('n25-cart-drawer');
        const drawerBody = document.getElementById('n25-drawer-body');
        const drawerClose = document.getElementById('n25-drawer-close');
        const drawerOverlay = document.getElementById('n25-drawer-overlay');
        const drawerCount = document.getElementById('n25-drawer-count');

        function openDrawer() {
            drawer.classList.add('is-active');
            loadDrawerContent();
        }

        function closeDrawer() {
            drawer.classList.remove('is-active');
        }

        function loadDrawerContent() {
            fetch(ajaxUrl + '?action=n25_get_cart_drawer')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        drawerBody.innerHTML = res.data.html;
                        if (drawerCount) drawerCount.innerText = res.data.count;
                    }
                });
        }

        if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
        if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

        // Click vào icon cart header mở Drawer
        document.querySelectorAll('.site-header-cart .cart-contents, a[href*="cart"]').forEach(el => {
            el.addEventListener('click', function (e) {
                // Chỉ chặn nếu ở ngoài trang /cart/ và /checkout/
                if (!window.location.href.includes('/cart/') && !window.location.href.includes('/checkout/')) {
                    e.preventDefault();
                    openDrawer();
                }
            });
        });

        // Tự động mở drawer khi add to cart thành công qua WooCommerce event
        jQuery(document.body).on('added_to_cart', function () {
            openDrawer();
        });

        // 2. QUICK VIEW MODAL
        const qvModal = document.getElementById('n25-quickview-modal');
        const qvBody = document.getElementById('n25-qv-body');
        const qvClose = document.getElementById('n25-qv-close');
        const qvOverlay = document.getElementById('n25-qv-overlay');

        function openQV(productId) {
            qvModal.classList.add('is-active');
            qvBody.innerHTML = '<div style="text-align:center;padding:40px;">⏳ Đang tải thông tin sản phẩm...</div>';
            fetch(ajaxUrl + '?action=n25_get_quickview&product_id=' + productId)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        qvBody.innerHTML = res.data.html;
                    } else {
                        qvBody.innerHTML = '<div style="padding:20px;color:red;">Không thể tải sản phẩm.</div>';
                    }
                });
        }

        function closeQV() {
            qvModal.classList.remove('is-active');
        }

        if (qvClose) qvClose.addEventListener('click', closeQV);
        if (qvOverlay) qvOverlay.addEventListener('click', closeQV);

        document.body.addEventListener('click', function (e) {
            if (e.target.classList.contains('n25-btn-quickview')) {
                e.preventDefault();
                const pid = e.target.getAttribute('data-product-id');
                if (pid) openQV(pid);
            }
        });

        // 3. LIVE SEARCH
        const searchInput = document.querySelector('.site-search input[type="search"]');
        if (searchInput) {
            const resultBox = document.createElement('div');
            resultBox.className = 'n25-search-results';
            searchInput.parentNode.style.position = 'relative';
            searchInput.parentNode.appendChild(resultBox);

            let searchTimeout = null;
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const kw = this.value.trim();
                if (kw.length < 2) {
                    resultBox.style.display = 'none';
                    resultBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(ajaxUrl + '?action=n25_live_search&keyword=' + encodeURIComponent(kw))
                        .then(r => r.json())
                        .then(res => {
                            if (res.success && res.data.results.length > 0) {
                                let html = '';
                                res.data.results.forEach(item => {
                                    html += `
                                        <a href="${item.url}" class="n25-search-item">
                                            <img src="${item.image || ''}" alt="${item.title}" />
                                            <div class="n25-search-item__info">
                                                <div class="n25-search-item__title">${item.title}</div>
                                                <div class="n25-search-item__price">${item.price}</div>
                                            </div>
                                        </a>
                                    `;
                                });
                                resultBox.innerHTML = html;
                                resultBox.style.display = 'block';
                            } else {
                                resultBox.innerHTML = '<div style="padding:14px;font-size:13px;color:#64748b;text-align:center;">Không tìm thấy sản phẩm phù hợp</div>';
                                resultBox.style.display = 'block';
                            }
                        });
                }, 250);
            });

            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !resultBox.contains(e.target)) {
                    resultBox.style.display = 'none';
                }
            });
        }

        // 4. XỬ LÝ NÚT "MUA NGAY" (BUY NOW)
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.n25-btn-buy-now');
            if (btn) {
                e.preventDefault();
                const form = btn.closest('form.cart');
                if (form) {
                    btn.innerHTML = '⏳ Đang chuyển thanh toán...';
                    btn.disabled = true;

                    const pid = btn.getAttribute('data-product-id');
                    
                    // Đảm bảo có input add-to-cart
                    let addInput = form.querySelector('input[name="add-to-cart"]');
                    if (!addInput) {
                        addInput = document.createElement('input');
                        addInput.type = 'hidden';
                        addInput.name = 'add-to-cart';
                        addInput.value = pid;
                        form.appendChild(addInput);
                    }

                    // Đảm bảo có flag n25_buy_now
                    let buyNowInput = form.querySelector('input[name="n25_buy_now"]');
                    if (!buyNowInput) {
                        buyNowInput = document.createElement('input');
                        buyNowInput.type = 'hidden';
                        buyNowInput.name = 'n25_buy_now';
                        buyNowInput.value = pid;
                        form.appendChild(buyNowInput);
                    }

                    form.submit();
                }
            }
        });
    });
    </script>
    <?php
}, 100);
