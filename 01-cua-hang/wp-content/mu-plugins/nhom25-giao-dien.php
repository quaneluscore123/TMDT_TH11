<?php
/*
 * Plugin Name: Nhom25 - Giao dien cua hang
 * Description: Nhan dien Bút Chì Xanh tren giao dien Storefront: dai thong bao, mau thuong hieu, the san pham co nhan
 *              "Đã kiểm định" va khoi lop, bo loc dang nut, trang chu co danh muc + san pham da kiem dinh + cam ket,
 *              chan trang nen dam. Chi them CSS va noi dung hien thi, khong doi du lieu hay luong mua hang.
 */

if (!defined('ABSPATH')) {
    exit;
}

// ---------- Dai thong bao tren dau trang ----------
add_action('wp_body_open', function () {
    if (is_admin()) {
        return;
    }
    echo '<div class="n25-topbar"><span>Miễn phí vận chuyển toàn quốc cho đơn từ 499.000đ</span>'
        . '<span class="n25-topbar__sep">·</span><span>Đổi trả 7 ngày</span>'
        . '<span class="n25-topbar__sep">·</span><span>Hỗ trợ 0901 234 567</span></div>';
});

// ---------- Nhan tren the san pham va trang chi tiet ----------
function nhom25_nhan_san_pham($product)
{
    if (!$product) {
        return '';
    }
    $out = '';
    if (has_term('co', 'pa_kiem-dinh-an-toan', $product->get_id())) {
        $out .= '<span class="n25-badge n25-badge--an-toan">✓ Đã kiểm định</span>';
    }
    $lop = $product->get_attribute('pa_khoi-lop');
    if ($lop && $lop !== 'Mọi lớp') {
        $out .= '<span class="n25-badge n25-badge--lop">' . esc_html($lop) . '</span>';
    }
    // Luon in khung nhan (ke ca rong) de cac the san pham cao bang nhau
    return '<div class="n25-badges">' . $out . '</div>';
}

add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    echo nhom25_nhan_san_pham($product);
}, 15);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    echo nhom25_nhan_san_pham($product);
}, 6);

// Bo 2 anh san pham truoc/sau bi cat o mep man hinh (Storefront)
add_action('wp', function () {
    remove_action('woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30);
});

// ---------- Trang chu: them cac khoi sau phan gioi thieu ----------
add_filter('the_content', function ($content) {
    if (!is_front_page() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $danh_muc = [
        'but-viet' => ['✏️', 'Bút bi, bút chì, bút mực'],
        'vo-so' => ['📒', 'Vở ô ly, sổ, giấy'],
        'dung-cu' => ['📐', 'Thước, compa, gọt bút chì'],
        'ba-lo-tui' => ['🎒', 'Ba lô chống gù, túi'],
        'my-thuat' => ['🎨', 'Màu vẽ, sáp màu, đất sét'],
    ];
    $the = '';
    foreach ($danh_muc as $slug => [$icon, $mo_ta]) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if (!$term) {
            continue;
        }
        $the .= '<a class="n25-cat" href="' . esc_url(get_term_link($term)) . '">'
            . '<span class="n25-cat__icon" aria-hidden="true">' . $icon . '</span>'
            . '<span class="n25-cat__name">' . esc_html($term->name) . '</span>'
            . '<span class="n25-cat__desc">' . esc_html($mo_ta) . '</span>'
            . '<span class="n25-cat__count">' . (int) $term->count . ' sản phẩm</span></a>';
    }

    $shop = wc_get_page_permalink('shop');
    $loc_an_toan = add_query_arg('pa_kiem-dinh-an-toan[]', 'co', $shop);

    $html = '<section class="n25-section"><div class="n25-section__head"><h2>Mua theo danh mục</h2></div>'
        . '<div class="n25-cats">' . $the . '</div></section>';

    $html .= '<section class="n25-section"><div class="n25-section__head"><h2>Đã kiểm định an toàn</h2>'
        . '<a class="n25-more" href="' . esc_url($loc_an_toan) . '">Xem tất cả →</a></div>'
        . do_shortcode('[products attribute="kiem-dinh-an-toan" terms="co" limit="4" columns="4" orderby="price" order="DESC"]')
        . '</section>';

    $html .= '<section class="n25-section"><div class="n25-section__head"><h2>Giá dễ chịu cho mọi ngày đến lớp</h2>'
        . '<a class="n25-more" href="' . esc_url(add_query_arg('price_range[]', '0-50000', $shop)) . '">Dưới 50.000đ →</a></div>'
        . do_shortcode('[products limit="4" columns="4" orderby="price" order="ASC"]')
        . '</section>';

    $html .= '<section class="n25-promise">'
        . '<div class="n25-promise__item"><b>Biết trước tổng tiền</b><span>Phí ship 5 vùng hiện ngay ở trang sản phẩm; miễn phí từ 499.000đ.</span></div>'
        . '<div class="n25-promise__item"><b>Đổi trả 7 ngày</b><span>Hàng lỗi cửa hàng chịu phí gửi về. <a href="' . esc_url(home_url('/chinh-sach-doi-tra/')) . '">Xem chính sách</a></span></div>'
        . '<div class="n25-promise__item"><b>Trả tiền theo cách bạn quen</b><span>COD, chuyển khoản VietQR hoặc thẻ qua VNPAY.</span></div>'
        . '<div class="n25-promise__item"><b>Theo dõi đơn dễ dàng</b><span>Chỉ cần mã đơn và email. <a href="' . esc_url(home_url('/tra-cuu-don-hang/')) . '">Tra cứu đơn</a></span></div>'
        . '</section>';

    return $content . $html;
}, 20);

// ---------- CSS ----------
add_action('wp_head', function () {
    ?>
    <style id="nhom25-giao-dien">
        :root {
            --n25-navy: #13304d; --n25-navy-2: #1f6e8c; --n25-teal: #2a9d8f; --n25-teal-d: #1f7f74;
            --n25-orange: #f4a261; --n25-bg: #f6f8fa; --n25-line: #e3e9ee; --n25-text: #2d3b48; --n25-muted: #5a6b7b;
        }
        body { background: var(--n25-bg); color: var(--n25-text); }
        h1, h2, h3, h4, .site-title a, .product_title, .woocommerce-loop-product__title { color: var(--n25-navy); }
        a { color: var(--n25-navy-2); }
        a:hover, a:focus { color: var(--n25-navy); }

        /* Dai thong bao */
        .n25-topbar { background: var(--n25-navy); color: #e8f0f6; text-align: center; font-size: 14px; padding: 8px 16px; line-height: 1.5; }
        .n25-topbar__sep { margin: 0 10px; opacity: .5; }

        /* Dau trang */
        .site-header { background: #fff; border-bottom: 1px solid var(--n25-line); padding-top: 1.4em; margin-bottom: 0; }
        .site-branding .site-title { font-weight: 800; letter-spacing: -.5px; }
        .site-branding .site-description { color: var(--n25-muted); }
        .site-search .widget_product_search input[type=search] { background: var(--n25-bg); border: 1px solid var(--n25-line); border-radius: 999px; box-shadow: none; }
        .main-navigation ul.menu > li > a, .main-navigation ul.nav-menu > li > a { color: var(--n25-navy); font-weight: 600; }
        .main-navigation ul.menu > li.current-menu-item > a, .main-navigation ul.menu > li > a:hover { color: var(--n25-teal-d); }
        .site-header-cart .cart-contents, .site-header-cart .cart-contents .amount { color: var(--n25-navy); font-weight: 600; }
        .storefront-breadcrumb { background: transparent; margin-bottom: 1.2em; padding: 1em 0 0; }
        .site-content { padding-top: 1.5em; }

        /* Nut */
        button, input[type=button], input[type=reset], input[type=submit], .button, .added_to_cart, .widget a.button {
            background: var(--n25-navy); border-color: var(--n25-navy); color: #fff; border-radius: 10px; font-weight: 600;
        }
        button:hover, input[type=submit]:hover, .button:hover, .added_to_cart:hover, .widget a.button:hover {
            background: var(--n25-navy-2); border-color: var(--n25-navy-2); color: #fff;
        }
        .single_add_to_cart_button.button, .checkout-button.button, #place_order, .wc-proceed-to-checkout .button {
            background: var(--n25-teal) !important; border-color: var(--n25-teal) !important;
        }
        .single_add_to_cart_button.button:hover, .checkout-button.button:hover, #place_order:hover { background: var(--n25-teal-d) !important; }
        input[type=text], input[type=email], input[type=tel], input[type=number], input[type=search], input[type=password], textarea, select, .select2-container .select2-selection--single {
            border-radius: 10px; border: 1px solid #c9d4dd; background: #fff; box-shadow: none;
        }
        input:focus, textarea:focus, select:focus { border-color: var(--n25-teal); outline: none; box-shadow: 0 0 0 2px rgba(42, 157, 143, .2); }

        /* Tieu de trang danh sach */
        .woocommerce-products-header { padding: 0 0 1em; text-align: left; }
        .woocommerce-products-header__title.page-title { font-size: 2.4em; font-weight: 700; color: var(--n25-navy); }
        .page-template-default:not(.home) .entry-header .entry-title, .woocommerce-cart .entry-title, .woocommerce-checkout .entry-title { font-weight: 700; }
        .home .hentry .entry-header { display: none; }
        .home .entry-content > div:first-child { border-radius: 20px !important; box-shadow: 0 16px 40px rgba(19, 48, 77, .15); }

        /* Bo loc (product-filters.php) */
        body .cpf-wrap { clear: both; background: #fff; border: 1px solid var(--n25-line); border-radius: 16px; padding: 0; margin: 8px 0 24px; }
        body .cpf-wrap .cpf-summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding: 16px 24px; }
        body .cpf-wrap .cpf-summary::-webkit-details-marker { display: none; }
        body .cpf-wrap .cpf-summary::after { content: "▾"; margin-left: auto; color: var(--n25-muted); transition: transform .2s; }
        body .cpf-wrap[open] .cpf-summary::after { transform: rotate(180deg); }
        body .cpf-wrap[open] .cpf-summary { border-bottom: 1px solid var(--n25-line); }
        body .cpf-wrap .cpf-summary__title { font-weight: 700; color: var(--n25-navy); }
        body .cpf-wrap .cpf-summary__title::before { content: "⚲"; display: inline-block; transform: rotate(-45deg); margin-right: 8px; color: var(--n25-teal); }
        body .cpf-wrap .cpf-summary__hint { color: var(--n25-muted); font-size: 14px; }
        body .cpf-wrap form { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 40px; align-items: start; padding: 20px 24px 22px; }
        body .cpf-wrap .cpf-group:nth-of-type(3) { grid-column: 1 / -1; }
        body .cpf-wrap .cpf-btn, body .cpf-wrap .cpf-status, body .cpf-wrap .cpf-reset { grid-column: 1 / -1; justify-self: start; }
        body .cpf-wrap .cpf-label { color: var(--n25-navy); font-size: 13px; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px; }
        body .cpf-wrap .cpf-opts { gap: 8px; }
        body .cpf-wrap .cpf-opts label { border: 1px solid #d5dee5; border-radius: 999px; padding: 5px 14px; background: #fff; color: var(--n25-text); font-size: 14px; transition: all .15s; }
        body .cpf-wrap .cpf-opts label:hover { border-color: var(--n25-teal); }
        body .cpf-wrap .cpf-opts label:has(input:checked) { background: #e8f4f2; border-color: var(--n25-teal); color: var(--n25-navy); font-weight: 600; }
        body .cpf-wrap input[type=checkbox] { accent-color: var(--n25-teal); width: 15px; height: 15px; }
        body .cpf-wrap .cpf-btn { background: var(--n25-navy); border-radius: 10px; padding: 10px 28px; margin-top: 8px; }
        body .cpf-wrap .cpf-btn:hover { background: var(--n25-navy-2); }
        body .cpf-wrap .cpf-status { color: var(--n25-teal-d); margin: 0; }
        body .cpf-wrap .cpf-reset { color: #b5482a; margin: 0; }
        .woocommerce-ordering select { padding: 8px 12px; border-radius: 10px; border: 1px solid #c9d4dd; background: #fff; }
        .storefront-sorting { margin-bottom: 1em; }
        .woocommerce-result-count { color: var(--n25-muted); }
        .woocommerce-pagination .page-numbers li .page-numbers { border-radius: 8px; }
        .woocommerce-pagination .page-numbers li .page-numbers.current { background: var(--n25-navy); color: #fff; }

        /* The san pham */
        ul.products li.product {
            background: #fff; border: 1px solid var(--n25-line); border-radius: 16px; padding: 14px 14px 18px;
            transition: box-shadow .2s, transform .2s; display: flex; flex-direction: column;
        }
        ul.products li.product:hover { box-shadow: 0 12px 28px rgba(19, 48, 77, .10); transform: translateY(-2px); }
        ul.products li.product img { border-radius: 10px; margin-bottom: .8em; }
        ul.products li.product .woocommerce-loop-product__title { font-size: 1rem; font-weight: 600; line-height: 1.35; min-height: 2.7em; }
        ul.products li.product .price { color: var(--n25-navy); font-weight: 700; font-size: 1.05rem; }
        ul.products li.product .button { display: block; margin-top: auto; }
        ul.products li.product .woocommerce-LoopProduct-link { display: block; }
        .n25-badges { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; margin: 0 0 8px; min-height: 22px; }
        .single-product .summary .n25-badges:empty { display: none; }
        .single-product .summary .n25-badges { justify-content: flex-start; margin: 0 0 14px; }
        .n25-badge { font-size: 12px; line-height: 1.6; padding: 2px 10px; border-radius: 999px; font-weight: 700; }
        .n25-badge--an-toan { background: #e8f4f2; color: var(--n25-teal-d); }
        .n25-badge--lop { background: #fdf1e6; color: #8f531c; }
        .onsale { border-color: var(--n25-orange); color: #8f531c; background: #fdf1e6; border-radius: 999px; }

        /* Trang chi tiet san pham */
        .single-product div.product .woocommerce-product-gallery img { border-radius: 16px; }
        .single-product div.product .product_title { font-weight: 700; font-size: 2.3em; margin-bottom: .3em; }
        .single-product div.product p.price { color: var(--n25-navy); font-size: 1.9em; font-weight: 700; }
        .single-product div.product form.cart .quantity .qty { border-radius: 10px; }
        .single-product div.product form.cart .single_add_to_cart_button { padding: .9em 2em; font-size: 1.05em; }
        .woocommerce-tabs ul.tabs li a { color: var(--n25-muted); }
        .woocommerce-tabs ul.tabs li.active a, .woocommerce-tabs ul.tabs li a:hover { color: var(--n25-navy); }
        .woocommerce-tabs ul.tabs li.active::after { color: var(--n25-teal); }
        .product_meta { color: var(--n25-muted); font-size: .9em; }
        .stock.in-stock { color: var(--n25-teal-d); }
        .storefront-product-pagination { display: none; }

        /* Thong bao */
        .woocommerce-message, .woocommerce-info, .woocommerce-noreviews, p.no-comments {
            background: #e8f4f2; color: var(--n25-navy); border-left: 4px solid var(--n25-teal); border-radius: 10px;
        }
        .woocommerce-message a, .woocommerce-info a { color: var(--n25-navy); text-decoration: underline; }
        .woocommerce-message::before, .woocommerce-info::before { color: var(--n25-teal-d); }
        .woocommerce-error { background: #fcece6; color: #7a2e14; border-left: 4px solid #d9623b; border-radius: 10px; }
        .woocommerce-error::before, .woocommerce-error a { color: #7a2e14; }

        /* Gio hang, thanh toan */
        table.shop_table, .cart_totals, #order_review { background: #fff; border-radius: 14px; }
        table.shop_table { border: 1px solid var(--n25-line); overflow: hidden; }
        table.shop_table th { color: var(--n25-navy); }
        .cart-empty.woocommerce-info { font-size: 1.1em; padding: 1.4em 1.6em 1.4em 3.5em; }
        .return-to-shop .button { background: var(--n25-teal); border-color: var(--n25-teal); }
        .woocommerce-checkout #payment { background: #fff; border: 1px solid var(--n25-line); border-radius: 14px; }
        .woocommerce-checkout #payment div.payment_box { background: var(--n25-bg); border-radius: 10px; color: var(--n25-text); }
        .woocommerce-checkout #payment div.payment_box::before { border-bottom-color: var(--n25-bg); }

        /* Trang noi dung (chinh sach, tra cuu don) */
        .page:not(.home):not(.woocommerce-page) .site-main article.page { background: #fff; border: 1px solid var(--n25-line); border-radius: 18px; padding: 2.4em 2.8em; }
        .page:not(.home) .entry-content table { border: 1px solid var(--n25-line); border-radius: 10px; overflow: hidden; }
        .page:not(.home) .entry-content table th { background: #eef3f6; color: var(--n25-navy); }
        .page:not(.home) .entry-content h2 { margin-top: 1.4em; font-weight: 700; }

        /* Trang chu: cac khoi */
        .n25-section { margin: 3.2em 0 0; }
        .n25-section__head { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; margin-bottom: 1.1em; }
        .n25-section__head h2 { margin: 0; font-size: 1.7em; font-weight: 700; }
        .n25-more, .n25-cat, .n25-cat span { text-decoration: none !important; }
        .n25-more { font-weight: 600; white-space: nowrap; color: var(--n25-teal-d); }
        .n25-cats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
        .n25-cat { display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1px solid var(--n25-line); border-radius: 16px; padding: 20px 18px; text-decoration: none; transition: box-shadow .2s, transform .2s, border-color .2s; }
        .n25-cat:hover { box-shadow: 0 12px 28px rgba(19, 48, 77, .10); transform: translateY(-2px); border-color: var(--n25-teal); }
        .n25-cat__icon { width: 52px; height: 52px; border-radius: 14px; background: #e8f4f2; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 8px; }
        .n25-cat__name { color: var(--n25-navy); font-weight: 700; font-size: 1.05em; }
        .n25-cat__desc { color: var(--n25-muted); font-size: .88em; line-height: 1.45; }
        .n25-cat__count { color: var(--n25-teal-d); font-size: .82em; font-weight: 700; margin-top: 6px; }
        .n25-section .woocommerce ul.products { margin-bottom: 0; }
        .n25-promise { margin: 3.2em 0 1em; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; background: #fff; border: 1px solid var(--n25-line); border-radius: 18px; overflow: hidden; }
        .n25-promise__item { padding: 24px 22px; display: flex; flex-direction: column; gap: 6px; border-right: 1px solid var(--n25-line); }
        .n25-promise__item:last-child { border-right: 0; }
        .n25-promise__item b { color: var(--n25-navy); font-size: 1.02em; }
        .n25-promise__item span { color: var(--n25-muted); font-size: .9em; line-height: 1.5; }

        /* Chan trang */
        .site-footer { background: var(--n25-navy) !important; color: #c9d6e2 !important; }
        .site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer .widget-title, .site-footer .widget .widget-title {
            color: #fff !important; border-bottom-color: rgba(255, 255, 255, .15) !important; font-weight: 600;
        }
        .site-footer a:not(.button):not(.components-button) { color: #fff !important; }
        .site-footer a:not(.button):hover { color: var(--n25-orange) !important; }
        .site-footer .widget ul li::before { color: rgba(255, 255, 255, .5); }
        .site-footer .site-info { border-top: 1px solid rgba(255, 255, 255, .12); color: #9fb3c6; }
        .site-footer [style*="border-top"] { border-top-color: rgba(255, 255, 255, .15) !important; }
        .site-footer [style*="color"] { color: #c9d6e2 !important; }
        .storefront-handheld-footer-bar ul li > a { background: var(--n25-navy); color: #fff; }

        @media (max-width: 767px) {
            html, body { overflow-x: hidden; }
            .n25-topbar { font-size: 13px; }
            .n25-topbar__sep, .n25-topbar span:nth-of-type(n+2) { display: none; }
            .n25-topbar span:first-child { display: inline; }
            .home .entry-content > div:first-child { padding: 30px 22px !important; gap: 8px !important; }
            .home .entry-content > div:first-child > * { flex: 1 1 100% !important; min-width: 0; max-width: 100% !important; }
            .home .entry-content > div:first-child h1 { font-size: 30px !important; }
            .home .entry-content > div:first-child p:first-of-type { font-size: 18px !important; }
            .home .entry-content > div:first-child ul { font-size: 16px !important; padding-top: 4px !important; }
            .n25-section__head h2 { font-size: 1.35em; }
            /* Luoi san pham 2 cot tren dien thoai */
            ul.products, .n25-section .woocommerce ul.products { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            ul.products::before, ul.products::after { display: none; }
            ul.products li.product { width: auto !important; float: none !important; margin: 0 !important; padding: 10px 10px 12px; border-radius: 14px; }
            ul.products li.product .woocommerce-loop-product__title { font-size: .9rem; min-height: 3.6em; }
            ul.products li.product .price { font-size: .95rem; }
            ul.products li.product .button { font-size: .82rem; padding: .7em .4em; }
            .n25-badge { font-size: 11px; padding: 1px 8px; }
            body .cpf-wrap { padding: 16px; }
            body .cpf-wrap form { grid-template-columns: 1fr; }
            .n25-cats { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .n25-promise { grid-template-columns: 1fr; }
            .n25-promise__item { border-right: 0; border-bottom: 1px solid var(--n25-line); }
            .n25-promise__item:last-child { border-bottom: 0; }
            .page:not(.home):not(.woocommerce-page) .site-main article.page { padding: 1.4em 1.2em; }
            .woocommerce-products-header__title.page-title { font-size: 1.8em; }
            .single-product div.product .product_title { font-size: 1.7em; }
        }
        @media (min-width: 768px) and (max-width: 1100px) {
            .n25-cats { grid-template-columns: repeat(3, 1fr); }
            .n25-promise { grid-template-columns: 1fr 1fr; }
        }
    </style>
    <?php
}, 50);
