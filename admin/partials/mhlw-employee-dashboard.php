<?php

/**
 * Employee Dashboard for MHLW Stress Check System
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$response = Mhlw_Stress_Check_Database::get_user_response($user->ID);
$draft_data = Mhlw_Stress_Check_Database::get_draft($user->ID);

// Get questions and response options
$questions = Mhlw_Stress_Check_Config::get_questions();
$response_options = Mhlw_Stress_Check_Config::get_response_options();
$domain_labels = Mhlw_Stress_Check_Config::get_domain_labels();
?>

<div class="wrap mhlw-employee-dashboard">
    <h1><?php _e('Stress Check Assessment', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <?php if ($response): ?>
        <!-- Already completed -->
        <div class="notice notice-success">
            <p>
                <strong><?php _e('Assessment Completed', 'mhlw-compliant-stress-check-system'); ?></strong><br>
                <?php printf(__('You completed this assessment on %s.', 'mhlw-compliant-stress-check-system'), 
                    date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?>
            </p>
        </div>
        
        <div class="mhlw-completion-summary">
            <h2><?php _e('Your Results Summary', 'mhlw-compliant-stress-check-system'); ?></h2>
            
            <div class="mhlw-result-status <?php echo $response->is_high_stress ? 'high-stress' : 'normal'; ?>">
                <h3>
                    <?php 
                    if ($response->is_high_stress) {
                        _e('High-Stress Individual', 'mhlw-compliant-stress-check-system');
                    } else {
                        _e('Not Applicable', 'mhlw-compliant-stress-check-system');
                    }
                    ?>
                </h3>
                <p>
                    <?php 
                    if ($response->is_high_stress) {
                        _e('Your stress check results indicate high levels of work-related stress.', 'mhlw-compliant-stress-check-system');
                    } else {
                        _e('Your stress check results do not indicate high levels of work-related stress.', 'mhlw-compliant-stress-check-system');
                    }
                    ?>
                </p>
            </div>
            
            <div class="mhlw-scores-grid">
                <div class="mhlw-score-box">
                    <div class="mhlw-score-label"><?php _e('Domain A', 'mhlw-compliant-stress-check-system'); ?></div>
                    <div class="mhlw-score-value"><?php echo esc_html($response->domain_a_score); ?></div>
                    <div class="mhlw-score-max">/ 68</div>
                </div>
                <div class="mhlw-score-box">
                    <div class="mhlw-score-label"><?php _e('Domain B', 'mhlw-compliant-stress-check-system'); ?></div>
                    <div class="mhlw-score-value"><?php echo esc_html($response->domain_b_score); ?></div>
                    <div class="mhlw-score-max">/ 116</div>
                </div>
                <div class="mhlw-score-box">
                    <div class="mhlw-score-label"><?php _e('Domain C', 'mhlw-compliant-stress-check-system'); ?></div>
                    <div class="mhlw-score-value"><?php echo esc_html($response->domain_c_score); ?></div>
                    <div class="mhlw-score-max">/ 44</div>
                </div>
            </div>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=mhlw-employee-results'); ?>" class="button button-primary">
                    <?php _e('View Detailed Results', 'mhlw-compliant-stress-check-system'); ?>
                </a>
            </p>
        </div>
        
    <?php else: ?>
        <!-- Assessment form -->
        <div class="mhlw-progress-container">
            <div class="mhlw-progress-bar">
                <div class="mhlw-progress-fill" style="width: 0%;"></div>
                <div class="mhlw-progress-text">0 / 57</div>
            </div>
        </div>
        
        <div class="mhlw-draft-status" style="display: none;">
            <span class="mhlw-draft-message"><?php _e('Draft saved', 'mhlw-compliant-stress-check-system'); ?></span>
        </div>
        
        <form id="mhlw-stress-check-form" method="post">
            <div class="mhlw-questions-container">
                <?php 
                $current_domain = '';
                foreach ($questions as $question_number => $question) : 
                    // Show domain header when domain changes
                    $question_domain = Mhlw_Stress_Check_Config::get_question_domain($question_number);
                    if ($question_domain !== $current_domain) :
                        $current_domain = $question_domain;
                ?>
                    <div class="mhlw-domain-header">
                        <h3><?php echo esc_html($domain_labels[$current_domain]); ?></h3>
                    </div>
                <?php endif; ?>
                
                <div class="mhlw-question-item" data-question="<?php echo esc_attr($question_number); ?>">
                    <div class="mhlw-question-number"><?php echo esc_html($question_number); ?>.</div>
                    <div class="mhlw-question-text"><?php echo esc_html($question['text']); ?></div>
                    
                    <div class="mhlw-response-options">
                        <?php foreach ($response_options as $value => $label) : 
                            $checked = (isset($draft_data[$question_number]) && $draft_data[$question_number] == $value) ? 'checked' : '';
                        ?>
                            <label class="mhlw-radio-label">
                                <input type="radio" 
                                       name="question_<?php echo esc_attr($question_number); ?>" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       <?php echo $checked; ?>
                                       required>
                                <span class="mhlw-radio-text"><?php echo esc_html($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            
            <div class="mhlw-form-actions">
                <button type="button" id="mhlw-save-draft" class="button button-secondary">
                    <?php _e('Save Draft', 'mhlw-compliant-stress-check-system'); ?>
                </button>
                <button type="submit" id="mhlw-submit-form" class="button button-primary">
                    <?php _e('Submit Assessment', 'mhlw-compliant-stress-check-system'); ?>
                </button>
            </div>
        </form>
        
        <div id="mhlw-form-messages"></div>
    <?php endif; ?>
</div>

<style>
.mhlw-employee-dashboard {
    max-width: 900px;
    margin: 0 auto;
}

.mhlw-progress-container {
    margin: 20px 0;
}

.mhlw-progress-bar {
    background: #e0e0e0;
    height: 30px;
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.mhlw-progress-fill {
    background: #2196F3;
    height: 100%;
    width: 0%;
    transition: width 0.3s ease;
}

.mhlw-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #333;
    font-weight: bold;
}

.mhlw-draft-status {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 10px;
    margin: 15px 0;
    text-align: center;
    border-radius: 4px;
}

.mhlw-domain-header {
    background: #f5f5f5;
    padding: 15px;
    margin: 20px 0 10px 0;
    border-left: 4px solid #2196F3;
}

.mhlw-domain-header h3 {
    margin: 0;
    color: #1d2327;
}

.mhlw-question-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 20px;
    margin: 15px 0;
    border-radius: 4px;
    transition: border-color 0.3s ease;
}

.mhlw-question-item.answered {
    border-color: #2196F3;
    background: #f8f9fa;
}

.mhlw-question-number {
    font-weight: bold;
    color: #2196F3;
    margin-bottom: 10px;
}

.mhlw-question-text {
    font-size: 16px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.mhlw-response-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.mhlw-radio-label {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 120px;
}

.mhlw-radio-label:hover {
    border-color: #2196F3;
    background: #f8f9fa;
}

.mhlw-radio-label input[type="radio"] {
    margin-right: 8px;
}

.mhlw-radio-label input[type="radio"]:checked + .mhlw-radio-text {
    color: #2196F3;
    font-weight: 500;
}

.mhlw-form-actions {
    margin: 30px 0;
    text-align: center;
}

.mhlw-form-actions button {
    margin: 0 10px;
    padding: 12px 30px;
}

.mhlw-form-messages {
    margin: 20px 0;
}

.mhlw-completion-summary {
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 25px;
    border-radius: 4px;
    margin: 20px 0;
}

.mhlw-result-status {
    text-align: center;
    padding: 25px;
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

.mhlw-result-status h3 {
    margin: 0 0 10px 0;
    font-size: 24px;
}

.mhlw-result-status.high-stress h3 {
    color: #c62828;
}

.mhlw-result-status.normal h3 {
    color: #2e7d32;
}

.mhlw-scores-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 25px 0;
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
    font-size: 32px;
    font-weight: bold;
    color: #2196F3;
}

.mhlw-score-max {
    font-size: 14px;
    color: #646970;
}
</style>

