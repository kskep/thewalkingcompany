<?php
/**
 * Front: Category Grid Section
 *
 * Displays a 2x2 grid of category containers with images, titles, and links
 * Uses ACF custom fields: FrontPage_Container_1, FrontPage_Container_2, FrontPage_Container_3, FrontPage_Container_4
 * Each container should have: image, title, and link subfields
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if ACF is available
if (!function_exists('get_field')) {
    return;
}

// Get the custom fields for each container
$containers = array();
$category_names = array('Shoes', 'Clothes', 'Accessories', 'Bags');
$subfield_patterns = array(
    1 => array('shoes_image', 'shoes_title', 'shoes_link'),
    2 => array('clothes_image', 'clothes_title', 'clothes_link'),
    3 => array('acc_image', 'acc_title', 'acc_link'),
    4 => array('bag_image', 'bag_title', 'bag_link')
);

for ($i = 1; $i <= 4; $i++) {
    $container_field = "FrontPage_Container_$i";
    $pattern = $subfield_patterns[$i];

    // Get the container group
    $container_group = get_field($container_field, 'option');

    if ($container_group && is_array($container_group)) {
        // Extract subfields from the group
        $image = $container_group[$pattern[0]] ?? null;
        $title = $container_group[$pattern[1]] ?? null;
        $link = $container_group[$pattern[2]] ?? null;

        // Check if we have the essential data
        if ($image && $title && $link) {
            $containers[] = array(
                'data' => array(
                    'image' => $image,
                    'title' => $title,
                    'link' => $link
                ),
                'index' => $i,
                'category' => $category_names[$i-1]
            );
        }
    }
}

// If no containers are found, show a placeholder message for admin users
if (empty($containers)) {
    if (current_user_can('manage_options')) {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
        echo '<strong>Admin Notice:</strong> Front page category grid is not configured properly.<br>';
        echo 'Please ensure each group field (FrontPage_Container_1, FrontPage_Container_2, etc.) contains the required subfields:<br>';
        echo '<ul class="mt-2 ml-4">';
        echo '<li>• FrontPage_Container_1: shoes_image, shoes_title, shoes_link</li>';
        echo '<li>• FrontPage_Container_2: clothes_image, clothes_title, clothes_link</li>';
        echo '<li>• FrontPage_Container_3: acc_image, acc_title, acc_link</li>';
        echo '<li>• FrontPage_Container_4: bag_image, bag_title, bag_link</li>';
        echo '</ul>';

        // Debug information
        echo '<div class="mt-4 text-sm">';
        echo '<strong>Debug Info:</strong><br>';
        for ($debug_i = 1; $debug_i <= 4; $debug_i++) {
            $debug_field = "FrontPage_Container_$debug_i";
            $debug_data = get_field($debug_field, 'option');
            echo "• $debug_field: " . (empty($debug_data) ? 'Empty/Not found' : 'Found (' . count($debug_data) . ' subfields)') . '<br>';
            if (!empty($debug_data) && is_array($debug_data)) {
                echo '&nbsp;&nbsp;Subfields: ' . implode(', ', array_keys($debug_data)) . '<br>';
            }
        }
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }
    return;
}
?>

<section class="category-grid-section py-8">
    <div class="container mx-auto px-4">
        <div class="category-grid grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($containers as $container_info) :
                $container = $container_info['data'];
                $index = $container_info['index'] - 1; // Convert to 0-based index
                $category_name = $container_info['category'];

                // Extract the data from each container
                $image = $container['image'] ?? null;
                $title = $container['title'] ?? $category_name; // Fallback to category name
                $link = $container['link'] ?? '#';

                // Skip if essential data is missing
                if (!$image || !$title) {
                    continue;
                }

                // Ensure image is an array with URL
                if (is_string($image)) {
                    $image = array('url' => $image, 'alt' => $title);
                } elseif (!is_array($image) || empty($image['url'])) {
                    continue;
                }

                // Determine category class based on index for styling
                $category_classes = array('shoes', 'clothes', 'accessories', 'bags');
                $category_class = $category_classes[$index] ?? 'default';
            ?>
                <div class="category-item group relative overflow-hidden">
                    <a href="<?php echo esc_url($link); ?>" class="block w-full h-full">
                        <div class="category-image-wrapper relative aspect-square">
                            <img 
                                src="<?php echo esc_url($image['url']); ?>" 
                                alt="<?php echo esc_attr($image['alt'] ?: $title); ?>"
                                class="category-image w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            />
                            
                            <!-- Overlay with title -->
                            <div class="category-overlay absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <h3 class="category-title text-white text-xl md:text-2xl font-bold text-center px-4">
                                    <?php echo esc_html($title); ?>
                                </h3>
                            </div>
                            
                            <!-- Bottom title bar (always visible) -->
                            <div class="category-title-bar absolute bottom-0 left-0 right-0 bg-white bg-opacity-90 p-3">
                                <h3 class="category-title-text text-dark text-lg font-semibold text-center">
                                    <?php echo esc_html($title); ?>
                                </h3>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
