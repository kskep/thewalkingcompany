<?php
/**
 * Simple test to check if menu is assigned and has items
 */

// Check if menu is assigned to primary location
$menu_locations = get_nav_menu_locations();
$primary_menu_id = isset($menu_locations['primary']) ? $menu_locations['primary'] : 0;

echo "<!-- Menu Debug Info -->\n";
echo "<!-- Primary menu ID: " . $primary_menu_id . " -->\n";

if ($primary_menu_id) {
    $menu_items = wp_get_nav_menu_items($primary_menu_id);
    echo "<!-- Menu items count: " . count($menu_items) . " -->\n";
    
    if (!empty($menu_items)) {
        echo "<!-- Menu has items -->\n";
    } else {
        echo "<!-- WARNING: Menu has no items -->\n";
    }
} else {
    echo "<!-- WARNING: No menu assigned to primary location -->\n";
}

// Output the menu directly for testing
echo "<!-- Direct menu output test -->\n";
if (has_nav_menu('primary')) {
    wp_nav_menu(array(
        'theme_location' => 'primary',
        'menu_id' => 'test-primary-menu',
        'menu_class' => 'flex space-x-8 text-sm font-medium uppercase tracking-wide',
        'container' => false,
        'fallback_cb' => false,
        'echo' => true,
    ));
} else {
    echo "<!-- No menu assigned to primary location -->\n";
}
?>