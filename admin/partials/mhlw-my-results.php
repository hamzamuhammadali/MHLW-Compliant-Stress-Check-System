<?php
/**
 * My Results Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */
?>

<div class="mhlw-results-container">
    <h2><?php _e('Your Stress Check Results', 'mhlw-compliant-stress-check-system'); ?></h2>
    
    <div class="mhlw-result-status <?php echo $scores['is_high_stress'] ? 'high-stress' : 'normal'; ?>">
        <h3>
            <?php 
            if ($scores['is_high_stress']) {
                _e('Classification: High-Stress Individual', 'mhlw-compliant-stress-check-system');
            } else {
                _e('Classification: Not Applicable', 'mhlw-compliant-stress-check-system');
            }
            ?>
        </h3>
        <p class="mhlw-result-date">
            <?php 
            printf(
                __('Completed on: %s', 'mhlw-compliant-stress-check-system'),
                esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at)))
            );
            ?>
        </p>
    </div>
    
    <div class="mhlw-domain-scores">
        <h4><?php _e('Domain Scores', 'mhlw-compliant-stress-check-system'); ?></h4>
        <div class="mhlw-score-grid">
            <div class="mhlw-score-item">
                <span class="mhlw-score-label"><?php _e('Domain A (Job Stressors)', 'mhlw-compliant-stress-check-system'); ?></span>
                <span class="mhlw-score-value"><?php echo esc_html($response->domain_a_score); ?> / 68</span>
            </div>
            <div class="mhlw-score-item">
                <span class="mhlw-score-label"><?php _e('Domain B (Stress Reactions)', 'mhlw-compliant-stress-check-system'); ?></span>
                <span class="mhlw-score-value"><?php echo esc_html($response->domain_b_score); ?> / 116</span>
            </div>
            <div class="mhlw-score-item">
                <span class="mhlw-score-label"><?php _e('Domain C (Social Support)', 'mhlw-compliant-stress-check-system'); ?></span>
                <span class="mhlw-score-value"><?php echo esc_html($response->domain_c_score); ?> / 44</span>
            </div>
            <div class="mhlw-score-item total">
                <span class="mhlw-score-label"><?php _e('Combined A + C', 'mhlw-compliant-stress-check-system'); ?></span>
                <span class="mhlw-score-value"><?php echo esc_html($response->domain_a_score + $response->domain_c_score); ?> / 112</span>
            </div>
        </div>
    </div>
    
    <div class="mhlw-scale-scores">
        <h4><?php _e('Scale Breakdown', 'mhlw-compliant-stress-check-system'); ?></h4>
        <div class="mhlw-scale-chart-container">
            <canvas id="mhlwScaleChart"></canvas>
        </div>
        <div class="mhlw-scale-table">
            <table>
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
    </div>
    
    <div class="mhlw-advice">
        <h4><?php _e('Advice', 'mhlw-compliant-stress-check-system'); ?></h4>
        <p><?php echo esc_html(Mhlw_Stress_Check_Scoring::get_advice($scores['is_high_stress'])); ?></p>
    </div>
    
    <div class="mhlw-result-actions">
        <a  class="mhlw-btn mhlw-btn-primary">
            <?php _e('Download PDF', 'mhlw-compliant-stress-check-system'); ?>
        </a>
    </div>
</div>

<style>
.mhlw-results-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.mhlw-result-status {
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
}

.mhlw-result-status.high-stress {
    background: #ffebee;
    border: 2px solid #f44336;
}

.mhlw-result-status.normal {
    background: #e8f5e9;
    border: 2px solid #4CAF50;
}

.mhlw-result-status h3 {
    margin: 0 0 10px 0;
}

.mhlw-result-status.high-stress h3 {
    color: #c62828;
}

.mhlw-result-status.normal h3 {
    color: #2e7d32;
}

.mhlw-result-date {
    margin: 0;
    color: #666;
}

.mhlw-domain-scores {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.mhlw-domain-scores h4 {
    margin-top: 0;
}

.mhlw-score-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.mhlw-score-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mhlw-score-item.total {
    background: #e3f2fd;
    border: 2px solid #2196F3;
}

.mhlw-score-label {
    font-weight: 500;
}

.mhlw-score-value {
    font-size: 18px;
    font-weight: bold;
    color: #2196F3;
}

.mhlw-scale-scores {
    margin: 20px 0;
}

.mhlw-scale-chart-container {
    max-width: 600px;
    margin: 20px auto;
}

.mhlw-scale-table {
    overflow-x: auto;
}

.mhlw-scale-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.mhlw-scale-table th,
.mhlw-scale-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.mhlw-scale-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.mhlw-advice {
    background: #fff3e0;
    border: 1px solid #ff9800;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.mhlw-advice h4 {
    margin-top: 0;
    color: #e65100;
}

.mhlw-result-actions {
    margin-top: 30px;
    text-align: center;
}

.mhlw-btn {
    display: inline-block;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.mhlw-btn-primary {
    background: #2196F3;
    color: white;
}

.mhlw-btn-primary:hover {
    background: #1976D2;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('mhlwScaleChart').getContext('2d');
    var scaleData = <?php echo json_encode($scores['scale_scores']); ?>;
    
    var labels = [];
    var data = [];
    
    for (var key in scaleData) {
        if (scaleData[key].count > 0) {
            labels.push(scaleData[key].name || key);
            data.push(scaleData[key].average);
        }
    }
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: '<?php _e("Your Scores", "mhlw-compliant-stress-check-system"); ?>',
                data: data,
                backgroundColor: 'rgba(33, 150, 243, 0.2)',
                borderColor: 'rgba(33, 150, 243, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(33, 150, 243, 1)',
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 4,
                    min: 0,
                    ticks: {
                        stepSize: 1
                    }
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
