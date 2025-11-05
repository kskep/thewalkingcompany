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
// Build containers from theme settings (no ACF)
$containers = function_exists('eshop_get_category_tiles') ? eshop_get_category_tiles() : array();

// If no containers are found, show a placeholder message for admin users
if (empty($containers)) {
    if (current_user_can('manage_options')) {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
        echo '<strong>Admin Notice:</strong> Front page category grid is not configured yet.<br>';
        echo 'Go to Appearance → Front Page and add Category Grid tiles.';
        echo '</div>';
        echo '</div>';
    }
    return;
}
?>

<section class="category-grid-section">
        <div class="category-grid grid grid-cols-1 md:grid-cols-2 gap-1">
            <?php foreach ($containers as $container_info) :
                $container = $container_info; // already normalized
                $index = ($container_info['index'] ?? 1) - 1; // Convert to 0-based index
                $category_name = $container_info['category'] ?? '';

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
                <div class="category-item relative overflow-hidden">
                    <a href="<?php echo esc_url($link); ?>" class="block w-full h-full">
                        <div class="category-image-wrapper relative aspect-square">
                            <img
                                src="<?php echo esc_url($image['url']); ?>"
                                alt="<?php echo esc_attr($image['alt'] ?: $title); ?>"
                                class="category-image w-full h-full object-cover"
                                loading="lazy"
                            />

                            <!-- Hidden title for SEO -->
                            <h3 class="sr-only">
                                <?php echo esc_html($title); ?>
                            </h3>

                            <!-- Data attribute for custom cursor -->
                            <span class="cursor-title" data-title="<?php echo esc_attr($title); ?>"></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
</section>

<!-- Custom cursor element -->
<div class="custom-cursor" id="customCursor"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cursor = document.getElementById('customCursor');
    const categoryItems = document.querySelectorAll('.category-item');

    // Track mouse movement
    document.addEventListener('mousemove', function(e) {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
    });

    // Handle category item hover
    categoryItems.forEach(item => {
        const titleElement = item.querySelector('.cursor-title');
        const title = titleElement ? titleElement.getAttribute('data-title') : '';

        item.addEventListener('mouseenter', function() {
            cursor.textContent = title;
            cursor.classList.add('active');
        });

        item.addEventListener('mouseleave', function() {
            cursor.classList.remove('active');
            cursor.textContent = '';
        });
    });

    // Hide cursor when leaving the grid section
    const gridSection = document.querySelector('.category-grid-section');
    if (gridSection) {
        gridSection.addEventListener('mouseleave', function() {
            cursor.classList.remove('active');
        });
    }
});
</script>
