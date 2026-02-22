<?php
/**
 * Debug AJAX Handler for MHLW Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add debug AJAX handler
function mhlw_debug_ajax() {
    header('Content-Type: application/json');
    
    $debug_info = array(
        'timestamp' => current_time('mysql'),
        'user_logged_in' => is_user_logged_in(),
        'user_id' => get_current_user_id(),
        'request_data' => $_POST,
        'server_info' => array(
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'HTTP_X_REQUESTED_WITH' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set',
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? 'not set',
        ),
        'nonce_check' => array(
            'nonce_sent' => isset($_POST['nonce']) ? $_POST['nonce'] : 'not sent',
            'nonce_valid' => isset($_POST['nonce']) ? wp_verify_nonce($_POST['nonce'], 'mhlw_stress_check_nonce') : 'not checked',
            'ajax_referer' => check_ajax_referer('mhlw_stress_check_nonce', 'nonce', false),
        ),
        'wp_functions' => array(
            'wp_create_nonce' => function_exists('wp_create_nonce'),
            'wp_verify_nonce' => function_exists('wp_verify_nonce'),
            'check_ajax_referer' => function_exists('check_ajax_referer'),
            'wp_send_json_success' => function_exists('wp_send_json_success'),
            'wp_send_json_error' => function_exists('wp_send_json_error'),
        )
    );
    
    wp_send_json_success($debug_info);
}

// Register the debug AJAX action
add_action('wp_ajax_mhlw_debug', 'mhlw_debug_ajax');
add_action('wp_ajax_nopriv_mhlw_debug', 'mhlw_debug_ajax');

// Also add a simple test page
function mhlw_debug_page() {
    ?>
    <div class="wrap">
        <h1>MHLW AJAX Debug</h1>
        
        <h3>Test AJAX Request</h3>
        <button type="button" id="test-ajax-btn">Test AJAX</button>
        
        <div id="debug-result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-ajax-btn').on('click', function() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'mhlw_debug',
                        nonce: '<?php echo wp_create_nonce('mhlw_stress_check_nonce'); ?>',
                        test_data: 'Hello from debug'
                    },
                    success: function(response) {
                        $('#debug-result').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                    },
                    error: function(xhr, status, error) {
                        $('#debug-result').html('<strong>Error:</strong> ' + status + ' - ' + error);
                    }
                });
            });
        });
        </script>
    </div>
    <?php
}

function mhlw_debug_menu() {
    add_submenu_page(
        'mhlw-dashboard',
        'AJAX Debug',
        'AJAX Debug',
        'manage_options',
        'mhlw-debug',
        'mhlw_debug_page'
    );
}

add_action('admin_menu', 'mhlw_debug_menu');
?>
