<?php
/**
 * Stress Check Form Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/public/partials
 */
?>

<div class="mhlw-stress-check-container">
    <h2><?php _e('Stress Check Assessment', 'mhlw-compliant-stress-check-system'); ?></h2>
    
    <div class="mhlw-progress-bar">
        <div class="mhlw-progress-fill" style="width: 0%;"></div>
        <span class="mhlw-progress-text">0 / 57</span>
    </div>
    
    <div class="mhlw-draft-status" style="display: none;">
        <span class="mhlw-draft-message"><?php _e('Draft saved', 'mhlw-compliant-stress-check-system'); ?></span>
    </div>
    
    <form id="mhlw-stress-check-form" method="post">
        <input type="hidden" name="mhlw_form_nonce" value="<?php echo wp_create_nonce('mhlw_stress_check_nonce'); ?>">
        
        <div class="mhlw-questions-container">
            <?php 
            $current_domain = '';
            foreach ($questions as $question_number => $question) : 
                // Show domain header when domain changes
                if ($question['domain'] !== $current_domain) :
                    $current_domain = $question['domain'];
                    $domain_labels = array(
                        'A' => __('Domain A: Job Stressors', 'mhlw-compliant-stress-check-system'),
                        'B' => __('Domain B: Stress Reactions', 'mhlw-compliant-stress-check-system'),
                        'C' => __('Domain C: Social Support & Other Factors', 'mhlw-compliant-stress-check-system'),
                    );
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
            <button type="button" id="mhlw-save-draft" class="mhlw-btn mhlw-btn-secondary">
                <?php _e('Save Draft', 'mhlw-compliant-stress-check-system'); ?>
            </button>
            <button type="submit" id="mhlw-submit-form" class="mhlw-btn mhlw-btn-primary">
                <?php _e('Submit Assessment', 'mhlw-compliant-stress-check-system'); ?>
            </button>
        </div>
    </form>
    
    <div id="mhlw-form-messages"></div>
</div>

<style>
.mhlw-stress-check-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.mhlw-progress-bar {
    background: #f0f0f0;
    border-radius: 10px;
    height: 30px;
    margin: 20px 0;
    position: relative;
    overflow: hidden;
}

.mhlw-progress-fill {
    background: linear-gradient(90deg, #4CAF50, #8BC34A);
    height: 100%;
    transition: width 0.3s ease;
}

.mhlw-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    color: #333;
}

.mhlw-draft-status {
    background: #e3f2fd;
    border: 1px solid #2196F3;
    border-radius: 4px;
    padding: 10px;
    margin: 10px 0;
}

.mhlw-draft-message {
    color: #1976D2;
    font-weight: 500;
}

.mhlw-domain-header {
    background: #f5f5f5;
    padding: 15px;
    margin: 30px 0 20px;
    border-left: 4px solid #2196F3;
}

.mhlw-domain-header h3 {
    margin: 0;
    color: #333;
}

.mhlw-question-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    transition: box-shadow 0.2s ease;
}

.mhlw-question-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.mhlw-question-item.answered {
    border-color: #4CAF50;
}

.mhlw-question-number {
    font-weight: bold;
    color: #2196F3;
    margin-bottom: 8px;
}

.mhlw-question-text {
    font-size: 16px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.mhlw-response-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.mhlw-radio-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    transition: all 0.2s ease;
    flex: 1;
    min-width: 120px;
}

.mhlw-radio-label:hover {
    border-color: #2196F3;
    background: #f5f5f5;
}

.mhlw-radio-label input[type="radio"] {
    margin-right: 8px;
}

.mhlw-radio-label input[type="radio"]:checked + .mhlw-radio-text {
    color: #2196F3;
    font-weight: 500;
}

.mhlw-radio-label:has(input[type="radio"]:checked) {
    border-color: #2196F3;
    background: #e3f2fd;
}

.mhlw-form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
}

.mhlw-btn {
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.mhlw-btn-primary {
    background: #2196F3;
    color: white;
}

.mhlw-btn-primary:hover {
    background: #1976D2;
}

.mhlw-btn-secondary {
    background: #757575;
    color: white;
}

.mhlw-btn-secondary:hover {
    background: #616161;
}

#mhlw-form-messages {
    margin-top: 20px;
}

.mhlw-message {
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.mhlw-message-success {
    background: #e8f5e9;
    border: 1px solid #4CAF50;
    color: #2e7d32;
}

.mhlw-message-error {
    background: #ffebee;
    border: 1px solid #f44336;
    color: #c62828;
}

@media (max-width: 600px) {
    .mhlw-response-options {
        flex-direction: column;
    }
    
    .mhlw-radio-label {
        width: 100%;
    }
}
</style>
