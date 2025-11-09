<?php
/**
 * Debug file to check menu configuration
 */

// Get all registered nav menus
$registered_menus = get_registered_nav_menus();
echo "<h2>Registered Navigation Menus:</h2>";
echo "<pre>";
print_r($registered_menus);
echo "</pre>";

// Get all menu locations
$menu_locations = get_nav_menu_locations();
echo "<h2>Menu Locations:</h2>";
echo "<pre>";
print_r($menu_locations);
echo "</pre>";

// Check if primary menu is assigned
if (isset($menu_locations['primary'])) {
    $primary_menu_id = $menu_locations['primary'];
    $primary_menu = wp_get_nav_menu_object($primary_menu_id);
    
    echo "<h2>Primary Menu Details:</h2>";
    echo "<pre>";
    print_r($primary_menu);
    echo "</pre>";
    
    // Get menu items
    if ($primary_menu) {
        $menu_items = wp_get_nav_menu_items($primary_menu->term_id);
        echo "<h2>Primary Menu Items:</h2>";
        echo "<pre>";
        print_r($menu_items);
        echo "</pre>";
    }
} else {
    echo "<h2>No menu assigned to primary location</h2>";
}

// Check theme mods for menu
$theme_mods = get_theme_mods();
echo "<h2>Theme Mods (menu related):</h2>";
$menu_related_mods = array_filter($theme_mods, function($key) {
    return strpos($key, 'nav_menu') !== false || strpos($key, 'menu') !== false;
}, ARRAY_FILTER_USE_KEY);
echo "<pre>";
print_r($menu_related_mods);
echo "</pre>";
?>