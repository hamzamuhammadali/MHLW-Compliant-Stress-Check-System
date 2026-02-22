<?php

/**
 * Employee Results Page for MHLW Stress Check System
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load WordPress admin scripts to ensure AJAX works
wp_enqueue_script('mhlw-compliant-stress-check-system-admin-js', plugin_dir_url(dirname(__FILE__)) . 'js/mhlw-compliant-stress-check-system-admin.js', array('jquery'), '1.0.0', false);
wp_localize_script('mhlw-compliant-stress-check-system-admin-js', 'mhlw_admin_ajax', array(
	'ajax_url' => admin_url('admin-ajax.php'),
	'nonce' => wp_create_nonce('mhlw_admin_nonce'),
));

// Add inline script to ensure downloadPDF function is available
?>
<script>
jQuery(document).ready(function($) {
    window.downloadPDF = function(responseId) {
        var downloadBtn = document.querySelector('button[onclick*="' + responseId + '"]');
        var originalText = downloadBtn.textContent;
        
        downloadBtn.disabled = true;
        downloadBtn.textContent = 'Generating PDF...';
        
        $.ajax({
            url: mhlw_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mhlw_generate_pdf',
                response_id: responseId,
                nonce: mhlw_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('PDF generated successfully');
                    console.log('Response:', response);
                    console.log('PDF URL:', response.data.pdf_url);
                    // Create direct download link instead of window.open
                    var link = document.createElement('a');
                    link.href = response.data.pdf_url;
                    link.target = '_blank';
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert(response.data && response.data.message ? response.data.message : 'Failed to generate PDF');
                }
                downloadBtn.disabled = false;
                downloadBtn.textContent = originalText;
            },
            error: function(xhr, status, error) {
                console.error('PDF generation error:', error);
                alert('Failed to generate PDF. Please try again.');
                downloadBtn.disabled = false;
                downloadBtn.textContent = originalText;
            }
        });
    };
});
</script>
<?php

$user = wp_get_current_user();
$response = Mhlw_Stress_Check_Database::get_user_response($user->ID);

if (!$response) {
    ?>
    <div class="wrap">
        <h1><?php _e('My Results', 'mhlw-compliant-stress-check-system'); ?></h1>
        <div class="notice notice-warning">
            <p><?php _e('You have not completed the stress check assessment yet.', 'mhlw-compliant-stress-check-system'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=mhlw-employee-assessment'); ?>" class="button button-primary">
                    <?php _e('Take Assessment', 'mhlw-compliant-stress-check-system'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
    return;
}

// Get response details and calculate scores
$response_details = Mhlw_Stress_Check_Database::get_response_details($response->id);
$scores = Mhlw_Stress_Check_Scoring::calculate_scores($response_details);

// Get user meta
$employee_id = get_user_meta($response->user_id, 'mhlw_employee_id', true);
$department = get_user_meta($response->user_id, 'mhlw_department_name', true);

// Generate PDF URL
$pdf_url = Mhlw_Stress_Check_PDF::generate_temp_url($response->id);
?>

<div class="wrap mhlw-employee-results">
    <h1><?php _e('My Results', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-results-header">
        <div class="mhlw-user-info">
            <p><strong><?php _e('Employee ID:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($employee_id ?: '-'); ?></p>
            <p><strong><?php _e('Department:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($department ?: '-'); ?></p>
            <p><strong><?php _e('Assessment Date:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?></p>
        </div>
        
        <div class="mhlw-actions">
            <button class="button button-primary" onclick="downloadPDF(<?php echo $response->id; ?>)" target="_blank">
                <?php _e('Download PDF', 'mhlw-compliant-stress-check-system'); ?>
            </button>
        </div>
    </div>
    
    <div class="mhlw-result-status <?php echo $scores['is_high_stress'] ? 'high-stress' : 'normal'; ?>">
        <h2>
            <?php 
            if ($scores['is_high_stress']) {
                _e('High-Stress Individual', 'mhlw-compliant-stress-check-system');
            } else {
                _e('Not Applicable', 'mhlw-compliant-stress-check-system');
            }
            ?>
        </h2>
        <p>
            <?php 
            if ($scores['is_high_stress']) {
                _e('Your stress check results indicate high levels of work-related stress. Please consider consulting with your supervisor or the designated health professional.', 'mhlw-compliant-stress-check-system');
            } else {
                _e('Your stress check results do not indicate high levels of work-related stress. Continue to monitor your work-life balance and seek support if needed.', 'mhlw-compliant-stress-check-system');
            }
            ?>
        </p>
    </div>
    
    <div class="mhlw-scores-section">
        <h2><?php _e('Domain Scores', 'mhlw-compliant-stress-check-system'); ?></h2>
        <div class="mhlw-scores-grid">
            <div class="mhlw-score-box">
                <div class="mhlw-score-label"><?php _e('Domain A', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Job Stressors', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="mhlw-score-value"><?php echo esc_html($response->domain_a_score); ?></div>
                <div class="mhlw-score-max">/ 68</div>
            </div>
            <div class="mhlw-score-box">
                <div class="mhlw-score-label"><?php _e('Domain B', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Stress Reactions', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="mhlw-score-value"><?php echo esc_html($response->domain_b_score); ?></div>
                <div class="mhlw-score-max">/ 116</div>
            </div>
            <div class="mhlw-score-box">
                <div class="mhlw-score-label"><?php _e('Domain C', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Social Support', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="mhlw-score-value"><?php echo esc_html($response->domain_c_score); ?></div>
                <div class="mhlw-score-max">/ 44</div>
            </div>
        </div>
        
        <div class="mhlw-combined-score">
            <p><strong><?php _e('Combined Domain A + C Score:', 'mhlw-compliant-stress-check-system'); ?></strong> 
               <?php echo esc_html($response->domain_a_score + $response->domain_c_score); ?> / 112</p>
        </div>
    </div>
    
    <div class="mhlw-scales-section">
        <h2><?php _e('Scale Breakdown', 'mhlw-compliant-stress-check-system'); ?></h2>
        <div class="mhlw-chart-container">
            <canvas id="mhlwScaleChart" width="400" height="400"></canvas>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Scale', 'mhlw-compliant-stress-check-system'); ?></th>
                    <th><?php _e('Average Score', 'mhlw-compliant-stress-check-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores['scale_scores'] as $scale_key => $scale_data) : ?>
                    <?php if ($scale_data['count'] > 0) : ?>
                        <tr>
                            <td><?php echo esc_html(Mhlw_Stress_Check_Scoring::get_scale_name($scale_key)); ?></td>
                            <td><?php echo esc_html($scale_data['average']); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mhlw-advice-section">
        <h2><?php _e('Advice and Recommendations', 'mhlw-compliant-stress-check-system'); ?></h2>
        <div class="mhlw-advice-content">
            <h4><?php _e('Based on Your Results', 'mhlw-compliant-stress-check-system'); ?></h4>
            <p><?php echo esc_html(Mhlw_Stress_Check_Scoring::get_advice($scores['is_high_stress'])); ?></p>
        </div>
    </div>
</div>

<style>
.mhlw-employee-results {
    max-width: 900px;
    margin: 0 auto;
}

.mhlw-results-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

.mhlw-user-info p {
    margin: 5px 0;
}

.mhlw-result-status {
    text-align: center;
    padding: 30px;
    margin: 20px 0;
    border-radius: 8px;
}

.mhlw-result-status.high-stress {
    background: #ffebee;
    border: 2px solid #d63638;
}

.mhlw-result-status.normal {
    background: #e8f5e9;
    border: 2px solid #008a20;
}

.mhlw-result-status h2 {
    margin: 0 0 15px 0;
    font-size: 28px;
}

.mhlw-result-status.high-stress h2 {
    color: #c62828;
}

.mhlw-result-status.normal h2 {
    color: #2e7d32;
}

.mhlw-scores-section {
    margin: 30px 0;
}

.mhlw-scores-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.mhlw-score-box {
    background: #f5f5f5;
    padding: 20px;
    text-align: center;
    border-radius: 4px;
}

.mhlw-score-label {
    font-size: 14px;
    color: #646970;
    margin-bottom: 5px;
}

.mhlw-score-value {
    font-size: 36px;
    font-weight: bold;
    color: #2196F3;
}

.mhlw-score-max {
    font-size: 14px;
    color: #646970;
}

.mhlw-combined-score {
    text-align: center;
    margin: 20px 0;
    padding: 15px;
    background: #f0f0f0;
    border-radius: 4px;
}

.mhlw-scales-section {
    margin: 30px 0;
}

.mhlw-chart-container {
    max-width: 400px;
    margin: 20px auto;
}

.mhlw-advice-section {
    margin: 30px 0;
}

.mhlw-advice-content {
    background: #e3f2fd;
    border: 1px solid #2196F3;
    padding: 20px;
    border-radius: 4px;
}

.mhlw-advice-content h4 {
    margin-top: 0;
    color: #1976D2;
}

@media (max-width: 768px) {
    .mhlw-results-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .mhlw-scores-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    // Radar chart data
    var scaleData = <?php echo json_encode(array_values($scores['scale_scores'])); ?>;
    var scaleLabels = <?php echo json_encode(array_map(function($key) {
        return Mhlw_Stress_Check_Scoring::get_scale_name($key);
    }, array_keys($scores['scale_scores']))); ?>;
    
    var ctx = document.getElementById('mhlwScaleChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: scaleLabels,
            datasets: [{
                label: '<?php _e("Your Scores", "mhlw-compliant-stress-check-system"); ?>',
                data: scaleData.map(function(item) { return item.average; }),
                backgroundColor: 'rgba(33, 150, 243, 0.2)',
                borderColor: 'rgba(33, 150, 243, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(33, 150, 243, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(33, 150, 243, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 4
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
