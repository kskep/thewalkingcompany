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
$field_patterns = array(
    1 => array('shoes_image', 'shoes_title', 'shoes_link'),
    2 => array('clothes_image', 'clothes_title', 'clothes_link'),
    3 => array('acc_image', 'acc_title', 'acc_link'),
    4 => array('bag_image', 'bag_title', 'bag_link')
);

for ($i = 1; $i <= 4; $i++) {
    $pattern = $field_patterns[$i];

    // Get individual fields directly (not from a group)
    $image = get_field($pattern[0]);
    $title = get_field($pattern[1]);
    $link = get_field($pattern[2]);

    // Use category name as fallback for title if title is empty
    if (empty($title)) {
        $title = $category_names[$i-1];
    }

    // Check if we have the essential data (image and link are required, title has fallback)
    if ($image && $link) {
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

// If no containers are found, show a placeholder message for admin users
if (empty($containers)) {
    if (current_user_can('manage_options')) {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
        echo '<strong>Admin Notice:</strong> Front page category grid is not configured properly.<br>';
        echo 'Please ensure the following individual fields are set up on this page:<br>';
        echo '<ul class="mt-2 ml-4">';
        echo '<li>• shoes_image, shoes_title (optional), shoes_link</li>';
        echo '<li>• clothes_image, clothes_title (optional), clothes_link</li>';
        echo '<li>• acc_image, acc_title (optional), acc_link</li>';
        echo '<li>• bag_image, bag_title (optional), bag_link</li>';
        echo '</ul>';
        echo '<p class="mt-2"><em>Note: Title fields are optional and will default to category names if empty.</em></p>';

        // Debug information
        echo '<div class="mt-4 text-sm">';
        echo '<strong>Debug Info:</strong><br>';
        $front_page_id = get_option('page_on_front');
        echo "Front page ID: " . ($front_page_id ?: 'Not set') . '<br>';

        // Let's check all fields on the front page to see what's actually there
        if ($front_page_id) {
            echo '<br><strong>All fields on front page:</strong><br>';
            $all_fields = get_fields($front_page_id);
            if ($all_fields) {
                foreach ($all_fields as $field_name => $field_value) {
                    echo "• $field_name: " . (is_array($field_value) ? 'Array (' . count($field_value) . ' items)' : gettype($field_value)) . '<br>';
                    if (is_array($field_value)) {
                        foreach ($field_value as $sub_key => $sub_value) {
                            echo "&nbsp;&nbsp;- $sub_key: " . (is_array($sub_value) ? 'Array' : gettype($sub_value)) . '<br>';
                        }
                    }
                }
            } else {
                echo "No fields found on front page.<br>";
            }
        }

        // Also check current post
        echo '<br><strong>All fields on current post:</strong><br>';
        $current_fields = get_fields();
        if ($current_fields) {
            foreach ($current_fields as $field_name => $field_value) {
                echo "• $field_name: " . (is_array($field_value) ? 'Array (' . count($field_value) . ' items)' : gettype($field_value)) . '<br>';
                if (is_array($field_value)) {
                    foreach ($field_value as $sub_key => $sub_value) {
                        echo "&nbsp;&nbsp;- $sub_key: " . (is_array($sub_value) ? 'Array' : gettype($sub_value)) . '<br>';
                    }
                }
            }
        } else {
            echo "No fields found on current post.<br>";
        }

        echo '</div>';

        echo '</div>';
        echo '</div>';
    }
    return;
}
?>

<section class="category-grid-section">
        <div class="category-grid grid grid-cols-1 md:grid-cols-2 gap-1">
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
