<?php
/**
 * Admin Dashboard Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Get statistics
$total_employees = count(get_users(array('role' => 'mhlw_employee')));
$total_responses = count(Mhlw_Stress_Check_Database::get_all_completed_responses());
$total_departments = count(Mhlw_Stress_Check_Database::get_all_departments());

// Calculate participation rate
$participation_rate = $total_employees > 0 ? round(($total_responses / $total_employees) * 100, 1) : 0;

// Get high-stress count
$high_stress_count = 0;
$responses = Mhlw_Stress_Check_Database::get_all_completed_responses();
foreach ($responses as $response) {
    if ($response['is_high_stress']) {
        $high_stress_count++;
    }
}
$high_stress_rate = $total_responses > 0 ? round(($high_stress_count / $total_responses) * 100, 1) : 0;
?>

<div class="wrap">
    <h1><?php _e('Stress Check System Dashboard', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-dashboard-stats">
        <div class="mhlw-stat-card">
            <h3><?php _e('Total Employees', 'mhlw-compliant-stress-check-system'); ?></h3>
            <div class="mhlw-stat-number"><?php echo esc_html($total_employees); ?></div>
        </div>
        
        <div class="mhlw-stat-card">
            <h3><?php _e('Completed Assessments', 'mhlw-compliant-stress-check-system'); ?></h3>
            <div class="mhlw-stat-number"><?php echo esc_html($total_responses); ?></div>
        </div>
        
        <div class="mhlw-stat-card">
            <h3><?php _e('Participation Rate', 'mhlw-compliant-stress-check-system'); ?></h3>
            <div class="mhlw-stat-number"><?php echo esc_html($participation_rate); ?>%</div>
        </div>
        
        <div class="mhlw-stat-card">
            <h3><?php _e('Departments', 'mhlw-compliant-stress-check-system'); ?></h3>
            <div class="mhlw-stat-number"><?php echo esc_html($total_departments); ?></div>
        </div>
        
        <div class="mhlw-stat-card high-stress">
            <h3><?php _e('High-Stress Individuals', 'mhlw-compliant-stress-check-system'); ?></h3>
            <div class="mhlw-stat-number"><?php echo esc_html($high_stress_count); ?></div>
            <div class="mhlw-stat-percentage"><?php echo esc_html($high_stress_rate); ?>% of respondents</div>
        </div>
    </div>
    
    <div class="mhlw-dashboard-sections">
        <div class="mhlw-section">
            <h2><?php _e('Quick Actions', 'mhlw-compliant-stress-check-system'); ?></h2>
            <div class="mhlw-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mhlw-import-employees')); ?>" class="button button-primary">
                    <?php _e('Import Employees', 'mhlw-compliant-stress-check-system'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mhlw-group-analysis')); ?>" class="button button-secondary">
                    <?php _e('View Group Analysis', 'mhlw-compliant-stress-check-system'); ?>
                </a>
                <?php if (current_user_can('mhlw_view_individual_results')) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mhlw-individual-results')); ?>" class="button button-secondary">
                        <?php _e('View Individual Results', 'mhlw-compliant-stress-check-system'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mhlw-section">
            <h2><?php _e('Recent Activity', 'mhlw-compliant-stress-check-system'); ?></h2>
            <?php
            // Get recent responses
            global $wpdb;
            $table_responses = $wpdb->prefix . 'mhlw_stress_responses';
            $recent_responses = $wpdb->get_results(
                "SELECT r.*, u.display_name 
                FROM $table_responses r 
                LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID 
                WHERE r.status = 'completed' 
                ORDER BY r.completed_at DESC 
                LIMIT 10"
            );
            
            if (!empty($recent_responses)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Employee', 'mhlw-compliant-stress-check-system'); ?></th>
                            <th><?php _e('Completion Date', 'mhlw-compliant-stress-check-system'); ?></th>
                            <th><?php _e('Status', 'mhlw-compliant-stress-check-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_responses as $response) : ?>
                            <tr>
                                <td><?php echo esc_html($response->display_name); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?></td>
                                <td>
                                    <?php if ($response->is_high_stress) : ?>
                                        <span class="mhlw-badge mhlw-badge-high"><?php _e('High Stress', 'mhlw-compliant-stress-check-system'); ?></span>
                                    <?php else : ?>
                                        <span class="mhlw-badge mhlw-badge-normal"><?php _e('Normal', 'mhlw-compliant-stress-check-system'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No completed assessments yet.', 'mhlw-compliant-stress-check-system'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.mhlw-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.mhlw-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.mhlw-stat-card.high-stress {
    border-color: #d63638;
    background: #fcf0f1;
}

.mhlw-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
}

.mhlw-stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #1d2327;
}

.mhlw-stat-card.high-stress .mhlw-stat-number {
    color: #d63638;
}

.mhlw-stat-percentage {
    font-size: 12px;
    color: #646970;
    margin-top: 5px;
}

.mhlw-dashboard-sections {
    margin-top: 30px;
}

.mhlw-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.mhlw-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.mhlw-quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.mhlw-quick-actions .button {
    padding: 10px 20px;
}

.mhlw-badge {
    display: inline-block;
    padding: 2px 8px;
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
</style>
