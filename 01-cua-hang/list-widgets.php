<?php
global $wp_widget_factory;
foreach ($wp_widget_factory->widgets as $id => $w) {
    echo $id . ': ' . $w->name . "\n";
}
