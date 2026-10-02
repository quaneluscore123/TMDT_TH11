<?php
$sidebars = get_option('sidebars_widgets');
if (!isset($sidebars['sidebar-1'])) $sidebars['sidebar-1'] = [];

$next_id = 100;
foreach (['pa_kiem-dinh-an-toan', 'pa_khoi-lop', 'pa_loai-san-pham'] as $attr) {
    $widget_key = 'wc_widget_layered_nav-' . $next_id;
    $sidebars['sidebar-1'][] = $widget_key;
    update_option('widget_wc_widget_layered_nav', array_merge(
        get_option('widget_wc_widget_layered_nav', []),
        [$next_id => [
            'title' => $attr === 'pa_kiem-dinh-an-toan' ? 'Kiểm định an toàn' : ($attr === 'pa_khoi-lop' ? 'Khối lớp' : 'Loại sản phẩm'),
            'attribute' => $attr,
            'display_type' => 'list',
            'query_type' => 'and',
            'count' => 1,
        ]]
    ));
    $next_id++;
}
update_option('sidebars_widgets', $sidebars);
echo "Widgets added to sidebar-1\n";
print_r($sidebars['sidebar-1']);
