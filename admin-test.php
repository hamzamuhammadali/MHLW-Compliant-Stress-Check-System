<?php
/**
 * Admin Test Page for MHLW Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Create admin menu item for testing
function mhlw_test_admin_menu() {
    add_submenu_page(
        'mhlw-dashboard',  // Parent slug (will be created when plugin is activated)
        'MHLW Test',
        'MHLW Test',
        'manage_options',
        'mhlw-test',
        'mhlw_test_page'
    );
}

// Render test page
function mhlw_test_page() {
    ?>
    <div class="wrap">
        <h1>MHLW Plugin Test Page</h1>
        
        <h3>Environment Check</h3>
        <table class="widefat">
            <tr><td>WordPress Version:</td><td><?php echo get_bloginfo('version'); ?></td></tr>
            <tr><td>PHP Version:</td><td><?php echo PHP_VERSION; ?></td></tr>
            <tr><td>Plugin Status:</td><td><?php echo is_plugin_active('mhlw-compliant-stress-check-system/mhlw-compliant-stress-check-system.php') ? 'Active' : 'Inactive'; ?></td></tr>
        </table>
        
        <h3>Class Check</h3>
        <table class="widefat">
            <tr><td>Config Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_Config') ? '✅ Found' : '❌ Missing'; ?></td></tr>
            <tr><td>Database Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_Database') ? '✅ Found' : '❌ Missing'; ?></td></tr>
            <tr><td>Scoring Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_Scoring') ? '✅ Found' : '❌ Missing'; ?></td></tr>
            <tr><td>Security Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_Security') ? '✅ Found' : '❌ Missing'; ?></td></tr>
            <tr><td>PDF Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_PDF') ? '✅ Found' : '❌ Missing'; ?></td></tr>
            <tr><td>User Fields Class:</td><td><?php echo class_exists('Mhlw_Stress_Check_User_Fields') ? '✅ Found' : '❌ Missing'; ?></td></tr>
        </table>
        
        <h3>Shortcode Test</h3>
        <table class="widefat">
            <tr><td>Form Shortcode:</td><td><?php echo shortcode_exists('mhlw_stress_check_form') ? '✅ Registered' : '❌ Not Registered'; ?></td></tr>
            <tr><td>Results Shortcode:</td><td><?php echo shortcode_exists('mhlw_my_results') ? '✅ Registered' : '❌ Not Registered'; ?></td></tr>
        </table>
        
        <h3>Database Tables</h3>
        <table class="widefat">
            <?php
            global $wpdb;
            $tables = array(
                'mhlw_departments',
                'mhlw_stress_responses',
                'mhlw_response_details',
                'mhlw_response_drafts',
                'mhlw_login_attempts'
            );
            
            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                echo '<tr><td>' . $table . ':</td><td>' . ($exists ? '✅ Exists' : '❌ Missing') . '</td></tr>';
            }
            ?>
        </table>
        
        <h3>Form Output Test</h3>
        <div style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
            <?php
            if (class_exists('Mhlw_Compliant_Stress_Check_System_Public')) {
                $public_class = new Mhlw_Compliant_Stress_Check_System_Public('mhlw-compliant-stress-check-system', '1.0.0');
                echo $public_class->render_stress_check_form();
            } else {
                echo '<p style="color: red;">❌ Public class not found</p>';
            }
            ?>
        </div>
    </div>
    <?php
}

// Hook into admin menu
add_action('admin_menu', 'mhlw_test_admin_menu');
?>
