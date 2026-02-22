<?php
/**
 * PDF Individual Result Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/templates
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php _e('Stress Check Result', 'mhlw-compliant-stress-check-system'); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24pt;
            margin: 0;
            color: #1d2327;
        }
        
        .header .subtitle {
            font-size: 14pt;
            color: #646970;
            margin-top: 10px;
        }
        
        .confidential {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            color: #856404;
        }
        
        .section {
            margin: 25px 0;
        }
        
        .section h2 {
            font-size: 16pt;
            color: #1d2327;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-table th {
            background: #f5f5f5;
            font-weight: 600;
            width: 40%;
        }
        
        .result-status {
            text-align: center;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .result-status.high-stress {
            background: #ffebee;
            border: 2px solid #d63638;
        }
        
        .result-status.normal {
            background: #e8f5e9;
            border: 2px solid #008a20;
        }
        
        .result-status h3 {
            margin: 0 0 10px 0;
            font-size: 20pt;
        }
        
        .result-status.high-stress h3 {
            color: #c62828;
        }
        
        .result-status.normal h3 {
            color: #2e7d32;
        }
        
        .scores-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .score-box {
            background: #f5f5f5;
            padding: 15px;
            text-align: center;
            border-radius: 4px;
        }
        
        .score-box .label {
            font-size: 10pt;
            color: #646970;
            margin-bottom: 5px;
        }
        
        .score-box .value {
            font-size: 24pt;
            font-weight: bold;
            color: #2196F3;
        }
        
        .score-box .max {
            font-size: 12pt;
            color: #646970;
        }
        
        .advice {
            background: #e3f2fd;
            border: 1px solid #2196F3;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .advice h4 {
            margin-top: 0;
            color: #1976D2;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 10pt;
            color: #646970;
            text-align: center;
        }
        
        .scale-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .scale-table th,
        .scale-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .scale-table th {
            background: #f5f5f5;
            font-weight: 600;
        }
        
        .scale-table td:last-child {
            text-align: right;
        }
        
        .company-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-logo img {
            max-width: 200px;
            max-height: 80px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <?php 
        // Allow company logo - can be customized via filter
        $company_logo = apply_filters('mhlw_pdf_company_logo', '');
        if ($company_logo) : 
        ?>
            <div class="company-logo">
                <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </div>
        <?php endif; ?>
        
        <h1><?php _e('Stress Check Assessment Report', 'mhlw-compliant-stress-check-system'); ?></h1>
        <div class="subtitle"><?php echo esc_html(get_bloginfo('name')); ?></div>
    </div>
    
    <div class="confidential">
        <?php _e('CONFIDENTIAL - For Personal Use Only', 'mhlw-compliant-stress-check-system'); ?>
    </div>
    
    <div class="section">
        <h2><?php _e('Employee Information', 'mhlw-compliant-stress-check-system'); ?></h2>
        <table class="info-table">
            <tr>
                <th><?php _e('Employee ID:', 'mhlw-compliant-stress-check-system'); ?></th>
                <td><?php echo esc_html($employee_id ?: '-'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Name:', 'mhlw-compliant-stress-check-system'); ?></th>
                <td><?php echo esc_html($user->display_name); ?></td>
            </tr>
            <tr>
                <th><?php _e('Department:', 'mhlw-compliant-stress-check-system'); ?></th>
                <td><?php echo esc_html($department ?: '-'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Assessment Date:', 'mhlw-compliant-stress-check-system'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2><?php _e('Assessment Result', 'mhlw-compliant-stress-check-system'); ?></h2>
        
        <div class="result-status <?php echo $scores['is_high_stress'] ? 'high-stress' : 'normal'; ?>">
            <h3>
                <?php 
                if ($scores['is_high_stress']) {
                    _e('High-Stress Individual', 'mhlw-compliant-stress-check-system');
                } else {
                    _e('Not Applicable', 'mhlw-compliant-stress-check-system');
                }
                ?>
            </h3>
            <p>
                <?php 
                if ($scores['is_high_stress']) {
                    _e('Your stress check results indicate high levels of work-related stress.', 'mhlw-compliant-stress-check-system');
                } else {
                    _e('Your stress check results do not indicate high levels of work-related stress.', 'mhlw-compliant-stress-check-system');
                }
                ?>
            </p>
        </div>
    </div>
    
    <div class="section">
        <h2><?php _e('Domain Scores', 'mhlw-compliant-stress-check-system'); ?></h2>
        <div class="scores-grid">
            <div class="score-box">
                <div class="label"><?php _e('Domain A', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Job Stressors', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="value"><?php echo esc_html($response->domain_a_score); ?></div>
                <div class="max">/ 68</div>
            </div>
            <div class="score-box">
                <div class="label"><?php _e('Domain B', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Stress Reactions', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="value"><?php echo esc_html($response->domain_b_score); ?></div>
                <div class="max">/ 116</div>
            </div>
            <div class="score-box">
                <div class="label"><?php _e('Domain C', 'mhlw-compliant-stress-check-system'); ?><br><?php _e('Social Support', 'mhlw-compliant-stress-check-system'); ?></div>
                <div class="value"><?php echo esc_html($response->domain_c_score); ?></div>
                <div class="max">/ 44</div>
            </div>
        </div>
        
        <table class="info-table">
            <tr>
                <th><?php _e('Combined Domain A + C Score:', 'mhlw-compliant-stress-check-system'); ?></th>
                <td><?php echo esc_html($response->domain_a_score + $response->domain_c_score); ?> / 112</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2><?php _e('Scale Breakdown', 'mhlw-compliant-stress-check-system'); ?></h2>
        <table class="scale-table">
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
    
    <div class="section">
        <h2><?php _e('Advice and Recommendations', 'mhlw-compliant-stress-check-system'); ?></h2>
        <div class="advice">
            <h4><?php _e('Based on Your Results', 'mhlw-compliant-stress-check-system'); ?></h4>
            <p><?php echo esc_html(Mhlw_Stress_Check_Scoring::get_advice($scores['is_high_stress'])); ?></p>
        </div>
    </div>
    
    <div class="footer">
        <p><?php _e('This report was generated in accordance with the Ministry of Health, Labour and Welfare (MHLW) Stress Check System guidelines.', 'mhlw-compliant-stress-check-system'); ?></p>
        <p><?php printf(__('Report ID: SC-%s | Generated: %s', 'mhlw-compliant-stress-check-system'), $response_id, date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></p>
    </div>
</body>
</html>
