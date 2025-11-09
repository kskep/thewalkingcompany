<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; }
        .debug-section h3 { margin-top: 0; color: #333; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Menu Debug Test</h1>
    
    <?php
    // Include WordPress
    require_once('wp-config.php');
    
    // Test 1: Check if WordPress is loaded
    echo '<div class="debug-section">';
    echo '<h3>WordPress Status</h3>';
    if (function_exists('wp_get_nav_menu_locations')) {
        echo '<p class="success">✓ WordPress functions loaded</p>';
    } else {
        echo '<p class="error">✗ WordPress functions not loaded</p>';
    }
    echo '</div>';
    
    // Test 2: Check registered menu locations
    echo '<div class="debug-section">';
    echo '<h3>Registered Menu Locations</h3>';
    $registered_menus = get_registered_nav_menus();
    if ($registered_menus) {
        echo '<pre>';
        print_r($registered_menus);
        echo '</pre>';
    } else {
        echo '<p class="error">No registered menu locations found</p>';
    }
    echo '</div>';
    
    // Test 3: Check assigned menus
    echo '<div class="debug-section">';
    echo '<h3>Assigned Menu Locations</h3>';
    $menu_locations = get_nav_menu_locations();
    if ($menu_locations) {
        echo '<pre>';
        print_r($menu_locations);
        echo '</pre>';
    } else {
        echo '<p class="warning">No menus assigned to locations</p>';
    }
    echo '</div>';
    
    // Test 4: Check primary menu specifically
    echo '<div class="debug-section">';
    echo '<h3>Primary Menu Details</h3>';
    if (isset($menu_locations['primary'])) {
        $primary_menu_id = $menu_locations['primary'];
        $primary_menu = wp_get_nav_menu_object($primary_menu_id);
        
        if ($primary_menu) {
            echo '<p class="success">✓ Primary menu found: ' . esc_html($primary_menu->name) . '</p>';
            echo '<p>Menu ID: ' . $primary_menu_id . '</p>';
            
            // Get menu items
            $menu_items = wp_get_nav_menu_items($primary_menu_id);
            if ($menu_items && !empty($menu_items)) {
                echo '<p class="success">✓ Menu has ' . count($menu_items) . ' items</p>';
                echo '<h4>Menu Items:</h4>';
                echo '<ol>';
                foreach ($menu_items as $item) {
                    echo '<li>' . esc_html($item->title) . ' (' . esc_url($item->url) . ')</li>';
                }
                echo '</ol>';
            } else {
                echo '<p class="warning">⚠ Menu exists but has no items</p>';
            }
        } else {
            echo '<p class="error">✗ Primary menu object not found</p>';
        }
    } else {
        echo '<p class="error">✗ No menu assigned to primary location</p>';
        echo '<p><strong>Solution:</strong> Go to WordPress Admin > Appearance > Menus and assign a menu to the "Primary Menu" location</p>';
    }
    echo '</div>';
    
    // Test 5: Check if menu function works
    echo '<div class="debug-section">';
    echo '<h3>Menu Function Test</h3>';
    if (has_nav_menu('primary')) {
        echo '<p class="success">✓ has_nav_menu(\'primary\') returns true</p>';
        
        // Try to output menu
        echo '<h4>Menu Output:</h4>';
        $menu_output = wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_id' => 'test-primary-menu',
            'menu_class' => 'flex space-x-8 text-sm font-medium uppercase tracking-wide',
            'container' => false,
            'fallback_cb' => false,
            'echo' => false,
        ));
        
        if ($menu_output) {
            echo '<div style="border: 1px solid #0f0; padding: 10px; background: #f0fff0;">';
            echo $menu_output;
            echo '</div>';
            echo '<p class="success">✓ Menu output generated successfully</p>';
        } else {
            echo '<p class="error">✗ Menu output is empty</p>';
        }
    } else {
        echo '<p class="error">✗ has_nav_menu(\'primary\') returns false</p>';
    }
    echo '</div>';
    
    // Test 6: Check CSS classes
    echo '<div class="debug-section">';
    echo '<h3>CSS Classes Test</h3>';
    echo '<p>Testing if CSS classes are working:</p>';
    echo '<div class="hidden lg:block" style="border: 1px solid blue; padding: 10px; margin: 10px 0;">';
    echo '<p>This div has "hidden lg:block" classes. It should be hidden on mobile and visible on desktop.</p>';
    echo '</div>';
    echo '<p>If you can see the blue box above on desktop, Tailwind classes are working.</p>';
    echo '</div>';
    ?>
    
    <div class="debug-section">
        <h3>Next Steps</h3>
        <ol>
            <li>If no menu is assigned to primary location: Go to WordPress Admin > Appearance > Menus and assign a menu</li>
            <li>If menu exists but has no items: Add menu items in the menu editor</li>
            <li>If CSS classes aren't working: Check if Tailwind CSS is loading properly</li>
            <li>If menu is assigned but not visible: Check for CSS conflicts or JavaScript hiding the menu</li>
        </ol>
    </div>
</body>
</html>