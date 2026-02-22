<?php
/**
 * Admin Individual Results Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Only accessible to Implementation Administrators
if (!current_user_can('mhlw_view_individual_results')) {
    wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
}

// Get all completed responses with user details
global $wpdb;
$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
$users_table = $wpdb->users;

$responses = $wpdb->get_results(
    "SELECT r.*, u.display_name, u.ID as user_id
    FROM $table_responses r
    LEFT JOIN $users_table u ON r.user_id = u.ID
    WHERE r.status = 'completed'
    ORDER BY r.completed_at DESC"
);

// Get user meta for additional details
$user_details = array();
foreach ($responses as $response) {
    $user_details[$response->user_id] = array(
        'employee_id' => get_user_meta($response->user_id, 'mhlw_employee_id', true),
        'department_name' => get_user_meta($response->user_id, 'mhlw_department_name', true),
        'org_level_1' => get_user_meta($response->user_id, 'mhlw_org_level_1', true),
    );
}

// Handle search/filter
$search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$filter_stress = isset($_GET['stress_filter']) ? sanitize_text_field($_GET['stress_filter']) : 'all';

// Filter responses based on search
if ($search_term || $filter_stress !== 'all') {
    $filtered_responses = array();
    foreach ($responses as $response) {
        // Search filter
        if ($search_term) {
            $employee_id = $user_details[$response->user_id]['employee_id'] ?? '';
            $display_name = $response->display_name ?? '';
            
            if (stripos($employee_id, $search_term) === false && 
                stripos($display_name, $search_term) === false) {
                continue;
            }
        }
        
        // Stress filter
        if ($filter_stress !== 'all') {
            $is_high_stress = ($response->is_high_stress == 1);
            if ($filter_stress === 'high' && !$is_high_stress) {
                continue;
            }
            if ($filter_stress === 'normal' && $is_high_stress) {
                continue;
            }
        }
        
        $filtered_responses[] = $response;
    }
    $responses = $filtered_responses;
}

// Pagination
$per_page = 20;
$total_items = count($responses);
$total_pages = ceil($total_items / $per_page);
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Slice for pagination
$responses = array_slice($responses, $offset, $per_page);
?>

<div class="wrap">
    <h1><?php _e('Individual Results', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-individual-results-container">
        <div class="mhlw-filter-bar">
            <form method="get" action="">
                <input type="hidden" name="page" value="mhlw-individual-results">
                
                <div class="mhlw-filters">
                    <div class="mhlw-filter-group">
                        <label for="s"><?php _e('Search:', 'mhlw-compliant-stress-check-system'); ?></label>
                        <input type="text" id="s" name="s" value="<?php echo esc_attr($search_term); ?>" 
                               placeholder="<?php _e('Employee ID or Name', 'mhlw-compliant-stress-check-system'); ?>">
                    </div>
                    
                    <div class="mhlw-filter-group">
                        <label for="stress_filter"><?php _e('Status:', 'mhlw-compliant-stress-check-system'); ?></label>
                        <select id="stress_filter" name="stress_filter">
                            <option value="all" <?php selected($filter_stress, 'all'); ?>>
                                <?php _e('All', 'mhlw-compliant-stress-check-system'); ?>
                            </option>
                            <option value="high" <?php selected($filter_stress, 'high'); ?>>
                                <?php _e('High Stress', 'mhlw-compliant-stress-check-system'); ?>
                            </option>
                            <option value="normal" <?php selected($filter_stress, 'normal'); ?>>
                                <?php _e('Normal', 'mhlw-compliant-stress-check-system'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <?php submit_button(__('Filter', 'mhlw-compliant-stress-check-system'), 'secondary', 'filter', false); ?>
                    
                    <?php if ($search_term || $filter_stress !== 'all') : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mhlw-individual-results')); ?>" class="button">
                            <?php _e('Clear Filters', 'mhlw-compliant-stress-check-system'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="mhlw-results-count">
            <?php printf(
                __('Showing %1$d of %2$d results', 'mhlw-compliant-stress-check-system'),
                count($responses),
                $total_items
            ); ?>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Employee ID', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Name', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Department', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Organization', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Completion Date', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Domain A', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Domain B', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Domain C', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Status', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Actions', 'mhlw-compliant-stress-check-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($responses)) : ?>
                    <tr>
                        <td colspan="10" class="mhlw-no-results">
                            <?php _e('No results found.', 'mhlw-compliant-stress-check-system'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($responses as $response) : 
                        $details = $user_details[$response->user_id] ?? array();
                    ?>
                        <tr>
                            <td><?php echo esc_html($details['employee_id'] ?? '-'); ?></td>
                            <td><?php echo esc_html($response->display_name); ?></td>
                            <td><?php echo esc_html($details['department_name'] ?? '-'); ?></td>
                            <td><?php echo esc_html($details['org_level_1'] ?? '-'); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($response->completed_at))); ?></td>
                            <td><?php echo esc_html($response->domain_a_score); ?></td>
                            <td><?php echo esc_html($response->domain_b_score); ?></td>
                            <td><?php echo esc_html($response->domain_c_score); ?></td>
                            <td>
                                <?php if ($response->is_high_stress) : ?>
                                    <span class="mhlw-badge mhlw-badge-high">
                                        <?php _e('High Stress', 'mhlw-compliant-stress-check-system'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="mhlw-badge mhlw-badge-normal">
                                        <?php _e('Normal', 'mhlw-compliant-stress-check-system'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(array(
                                    'page' => 'mhlw-individual-results',
                                    'view' => $response->id,
                                ), admin_url('admin.php'))); ?>" class="button button-small">
                                    <?php _e('View Details', 'mhlw-compliant-stress-check-system'); ?>
                                </a>
                                <a href="<?php echo esc_url(add_query_arg(array(
                                    'page' => 'mhlw-individual-results',
                                    'download_pdf' => $response->id,
                                ), admin_url('admin.php'))); ?>" class="button button-small">
                                    <?php _e('PDF', 'mhlw-compliant-stress-check-system'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(__('%s items', 'mhlw-compliant-stress-check-system'), number_format_i18n($total_items)); ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $current_page,
                        ));
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mhlw-confidentiality-notice">
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Confidentiality Notice:', 'mhlw-compliant-stress-check-system'); ?></strong>
                <?php _e('This page contains confidential personal information. Access is restricted to Implementation Administrators (Industrial Physicians and Designated Personnel) only. Handle this information with extreme care and in compliance with privacy regulations.', 'mhlw-compliant-stress-check-system'); ?>
            </p>
        </div>
    </div>
</div>

<style>
.mhlw-individual-results-container {
    margin: 20px 0;
}

.mhlw-filter-bar {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.mhlw-filters {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.mhlw-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.mhlw-filter-group label {
    font-weight: 500;
}

.mhlw-filter-group input,
.mhlw-filter-group select {
    min-width: 150px;
}

.mhlw-results-count {
    margin: 15px 0;
    color: #646970;
}

.mhlw-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.mhlw-badge-high {
    background: #fcf0f1;
    color: #d63638;
    border: 1px solid #d63638;
}

.mhlw-badge-normal {
    background: #edfaef;
    color: #008a20;
    border: 1px solid #008a20;
}

.mhlw-no-results {
    text-align: center;
    padding: 20px;
    color: #646970;
}

.mhlw-confidentiality-notice {
    margin-top: 30px;
}

.tablenav {
    margin-top: 15px;
}
</style>
