<?php
/**
 * Admin Import Employees Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Get import results if available
$import_results = get_transient('mhlw_import_results');
delete_transient('mhlw_import_results');
?>

<div class="wrap">
    <h1><?php _e('Import Employees', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-import-container">
        <div class="mhlw-import-section">
            <h2><?php _e('CSV File Format', 'mhlw-compliant-stress-check-system'); ?></h2>
            <p><?php _e('The CSV file should contain the following columns in order:', 'mhlw-compliant-stress-check-system'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Column', 'mhlw-compliant-stress-check-system'); ?></th>
                        <th><?php _e('Description', 'mhlw-compliant-stress-check-system'); ?></th>
                        <th><?php _e('Required', 'mhlw-compliant-stress-check-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>Employee ID</code></td>
                        <td><?php _e('Unique employee identifier', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><span class="mhlw-required"><?php _e('Yes', 'mhlw-compliant-stress-check-system'); ?></span></td>
                    </tr>
                    <tr>
                        <td><code>Name</code></td>
                        <td><?php _e('Employee full name', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><?php _e('No', 'mhlw-compliant-stress-check-system'); ?></td>
                    </tr>
                    <tr>
                        <td><code>Department ID</code></td>
                        <td><?php _e('Unique department identifier', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><span class="mhlw-required"><?php _e('Yes', 'mhlw-compliant-stress-check-system'); ?></span></td>
                    </tr>
                    <tr>
                        <td><code>Department Name</code></td>
                        <td><?php _e('Department name', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><?php _e('No', 'mhlw-compliant-stress-check-system'); ?></td>
                    </tr>
                    <tr>
                        <td><code>Organization Level 1</code></td>
                        <td><?php _e('Top-level organization (e.g., Division)', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><?php _e('No', 'mhlw-compliant-stress-check-system'); ?></td>
                    </tr>
                    <tr>
                        <td><code>Organization Level 2</code></td>
                        <td><?php _e('Mid-level organization (e.g., Branch)', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><?php _e('No', 'mhlw-compliant-stress-check-system'); ?></td>
                    </tr>
                    <tr>
                        <td><code>Organization Level 3</code></td>
                        <td><?php _e('Lower-level organization (e.g., Section)', 'mhlw-compliant-stress-check-system'); ?></td>
                        <td><?php _e('No', 'mhlw-compliant-stress-check-system'); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mhlw-sample-csv">
                <h3><?php _e('Sample CSV Data', 'mhlw-compliant-stress-check-system'); ?></h3>
                <pre>Employee ID,Name,Department ID,Department Name,Organization Level 1,Organization Level 2,Organization Level 3
EMP001,John Smith,DEPT001,Sales Department,Headquarters,Tokyo Branch,Sales Section
EMP002,Jane Doe,DEPT001,Sales Department,Headquarters,Tokyo Branch,Sales Section
EMP003,Bob Johnson,DEPT002,HR Department,Headquarters,Osaka Branch,HR Section</pre>
            </div>
        </div>
        
        <div class="mhlw-import-section">
            <h2><?php _e('Upload CSV File', 'mhlw-compliant-stress-check-system'); ?></h2>
            
            <?php settings_errors('mhlw_import'); ?>
            
            <?php if ($import_results) : ?>
                <div class="mhlw-import-results">
                    <h3><?php _e('Import Results', 'mhlw-compliant-stress-check-system'); ?></h3>
                    <div class="mhlw-results-summary">
                        <span class="mhlw-result-item success">
                            <?php printf(__('%d employees imported successfully', 'mhlw-compliant-stress-check-system'), $import_results['imported']); ?>
                        </span>
                        <?php if ($import_results['errors'] > 0) : ?>
                            <span class="mhlw-result-item error">
                                <?php printf(__('%d errors', 'mhlw-compliant-stress-check-system'), $import_results['errors']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($import_results['error_messages'])) : ?>
                        <div class="mhlw-error-details">
                            <h4><?php _e('Error Details', 'mhlw-compliant-stress-check-system'); ?></h4>
                            <ul>
                                <?php foreach ($import_results['error_messages'] as $error) : ?>
                                    <li><?php echo esc_html($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('mhlw_import_employees', 'mhlw_import_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php _e('CSV File', 'mhlw-compliant-stress-check-system'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <p class="description">
                                <?php _e('Maximum file size: ', 'mhlw-compliant-stress-check-system'); ?>
                                <?php echo esc_html(ini_get('upload_max_filesize')); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Import Employees', 'mhlw-compliant-stress-check-system')); ?>
            </form>
        </div>
        
        <div class="mhlw-import-section">
            <h2><?php _e('Current Employee Count', 'mhlw-compliant-stress-check-system'); ?></h2>
            <?php
            $total_employees = count(get_users(array('role' => 'mhlw_employee')));
            $total_departments = count(Mhlw_Stress_Check_Database::get_all_departments());
            ?>
            <div class="mhlw-current-stats">
                <div class="mhlw-stat-item">
                    <span class="mhlw-stat-label"><?php _e('Total Employees:', 'mhlw-compliant-stress-check-system'); ?></span>
                    <span class="mhlw-stat-value"><?php echo esc_html($total_employees); ?></span>
                </div>
                <div class="mhlw-stat-item">
                    <span class="mhlw-stat-label"><?php _e('Total Departments:', 'mhlw-compliant-stress-check-system'); ?></span>
                    <span class="mhlw-stat-value"><?php echo esc_html($total_departments); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mhlw-import-container {
    max-width: 800px;
}

.mhlw-import-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.mhlw-import-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.mhlw-required {
    color: #d63638;
    font-weight: bold;
}

.mhlw-sample-csv {
    margin-top: 20px;
}

.mhlw-sample-csv pre {
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    overflow-x: auto;
    font-size: 12px;
}

.mhlw-import-results {
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.mhlw-results-summary {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.mhlw-result-item {
    padding: 8px 15px;
    border-radius: 4px;
    font-weight: 500;
}

.mhlw-result-item.success {
    background: #edfaef;
    color: #008a20;
    border: 1px solid #008a20;
}

.mhlw-result-item.error {
    background: #fcf0f1;
    color: #d63638;
    border: 1px solid #d63638;
}

.mhlw-error-details {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #c3c4c7;
}

.mhlw-error-details ul {
    color: #d63638;
    margin: 10px 0;
    padding-left: 20px;
}

.mhlw-current-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.mhlw-stat-item {
    font-size: 16px;
}

.mhlw-stat-label {
    color: #646970;
}

.mhlw-stat-value {
    font-weight: bold;
    color: #1d2327;
    margin-left: 10px;
}
</style>
