<?php
/**
 * Individual Detail View Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Only accessible to Implementation Administrators
if (!current_user_can('mhlw_view_individual_results')) {
    wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
}
?>

<div class="wrap">
    <h1><?php _e('Individual Result Details', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-detail-header">
        <div class="mhlw-employee-info">
            <p><strong><?php _e('Employee ID:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($user_details['employee_id'] ?: '-'); ?></p>
            <p><strong><?php _e('Name:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($user->display_name); ?></p>
            <p><strong><?php _e('Department:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($user_details['department_name'] ?: '-'); ?></p>
            <p><strong><?php _e('Organization:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html($user_details['org_level_1'] ?: '-'); ?></p>
            <p><strong><?php _e('Assessment Date:', 'mhlw-compliant-stress-check-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?></p>
        </div>
        
        <div class="mhlw-scores-summary">
            <div class="mhlw-score-card <?php echo $scores['is_high_stress'] ? 'high-stress' : 'normal'; ?>">
                <h3><?php _e('Overall Status', 'mhlw-compliant-stress-check-system'); ?></h3>
                <p><?php echo $scores['is_high_stress'] ? __('High-Stress Individual', 'mhlw-compliant-stress-check-system') : __('Not Applicable', 'mhlw-compliant-stress-check-system'); ?></p>
            </div>
            
            <div class="mhlw-scores-grid">
                <div class="mhlw-score-item">
                    <h4><?php _e('Domain A', 'mhlw-compliant-stress-check-system'); ?></h4>
                    <p class="score"><?php echo esc_html($response->domain_a_score); ?></p>
                </div>
                <div class="mhlw-score-item">
                    <h4><?php _e('Domain B', 'mhlw-compliant-stress-check-system'); ?></h4>
                    <p class="score"><?php echo esc_html($response->domain_b_score); ?></p>
                </div>
                <div class="mhlw-score-item">
                    <h4><?php _e('Domain C', 'mhlw-compliant-stress-check-system'); ?></h4>
                    <p class="score"><?php echo esc_html($response->domain_c_score); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mhlw-detail-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=mhlw-individual-results')); ?>" class="button">
            ← <?php _e('Back to List', 'mhlw-compliant-stress-check-system'); ?>
        </a>
        <a href="#" onclick="downloadPDF(<?php echo $response->id; ?>); return false;" class="button button-primary">
            <?php _e('Download PDF', 'mhlw-compliant-stress-check-system'); ?>
        </a>
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
.mhlw-detail-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin: 20px 0;
}

.mhlw-employee-info p {
    margin: 10px 0;
}

.mhlw-scores-summary {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.mhlw-score-card {
    text-align: center;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.mhlw-score-card.high-stress {
    background: #fcf0f1;
    border: 1px solid #d63638;
    color: #d63638;
}

.mhlw-score-card.normal {
    background: #edfaef;
    border: 1px solid #008a20;
    color: #008a20;
}

.mhlw-scores-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.mhlw-score-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border: 1px solid #ddd;
}

.mhlw-score-item h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}

.mhlw-score-item .score {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}

.mhlw-detail-actions {
    margin: 20px 0;
    display: flex;
    gap: 10px;
}

.mhlw-confidentiality-notice {
    margin-top: 30px;
}
</style>

<script>
jQuery(document).ready(function($) {
    window.downloadPDF = function(responseId) {
        var downloadBtn = event.target;
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
                    // Open in new window for printing/saving
                    window.open(response.data.pdf_url, '_blank');
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
