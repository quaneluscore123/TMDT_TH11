<?php
// Xoa tat ca widget khoi sidebar-1 (site goc khong co widget sidebar)
$sidebars = get_option('sidebars_widgets');
if (!is_array($sidebars)) $sidebars = [];
$sidebars['sidebar-1'] = [];
update_option('sidebars_widgets', $sidebars);
echo 'sidebar-1 widgets: ' . json_encode($sidebars['sidebar-1']) . "\n";
echo "=== Sidebar cleared ===\n";
