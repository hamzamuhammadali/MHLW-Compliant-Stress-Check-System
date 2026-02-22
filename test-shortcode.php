<?php
/**
 * Simple test for shortcode processing
 */

// Check if WordPress is loaded
if (!function_exists('add_shortcode')) {
    echo 'WordPress not loaded properly';
    exit;
}

// Test basic PHP processing
echo '<h3>PHP Test:</h3>';
echo 'Current time: ' . date('Y-m-d H:i:s');
echo '<br>';

// Test WordPress functions
echo '<h3>WordPress Test:</h3>';
echo 'WordPress version: ' . get_bloginfo('version');
echo '<br>';

// Test if our class exists
if (class_exists('Mhlw_Compliant_Stress_Check_System_Public')) {
    echo 'MHLW Public class: LOADED';
} else {
    echo 'MHLW Public class: NOT FOUND';
}

// Test shortcode registration
echo '<h3>Shortcode Test:</h3>';
if (shortcode_exists('mhlw_stress_check_form')) {
    echo 'mhlw_stress_check_form shortcode: REGISTERED';
} else {
    echo 'mhlw_stress_check_form shortcode: NOT REGISTERED';
}

echo '<br><br>';
echo '<h3>Manual Form Test:</h3>';
echo '<form><button type="submit">Test Button</button></form>';
echo '<br>';
echo '<?php _e("Submit Assessment", "mhlw-compliant-stress-check-system"); ?>';
?>
