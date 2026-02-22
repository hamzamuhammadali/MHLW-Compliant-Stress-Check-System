<?php
/**
 * Admin Group Analysis Template
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin/partials
 */

// Get filter options
$departments = Mhlw_Stress_Check_Database::get_all_departments();

// Get unique organization levels
$org_levels = array('1' => array(), '2' => array(), '3' => array());
foreach ($departments as $dept) {
    if (!empty($dept->org_level_1)) $org_levels['1'][$dept->org_level_1] = $dept->org_level_1;
    if (!empty($dept->org_level_2)) $org_levels['2'][$dept->org_level_2] = $dept->org_level_2;
    if (!empty($dept->org_level_3)) $org_levels['3'][$dept->org_level_3] = $dept->org_level_3;
}
?>

<div class="wrap">
    <h1><?php _e('Group Analysis', 'mhlw-compliant-stress-check-system'); ?></h1>
    
    <div class="mhlw-group-analysis-container">
        <div class="mhlw-filter-section">
            <h2><?php _e('Filter Options', 'mhlw-compliant-stress-check-system'); ?></h2>
            
            <div class="mhlw-filter-controls">
                <div class="mhlw-filter-group">
                    <label for="filter_type"><?php _e('Analysis Unit:', 'mhlw-compliant-stress-check-system'); ?></label>
                    <select id="filter_type" name="filter_type">
                        <option value="company"><?php _e('Company-wide', 'mhlw-compliant-stress-check-system'); ?></option>
                        <option value="department"><?php _e('By Department', 'mhlw-compliant-stress-check-system'); ?></option>
                        <option value="1"><?php _e('By Organization Level 1', 'mhlw-compliant-stress-check-system'); ?></option>
                        <option value="2"><?php _e('By Organization Level 2', 'mhlw-compliant-stress-check-system'); ?></option>
                        <option value="3"><?php _e('By Organization Level 3', 'mhlw-compliant-stress-check-system'); ?></option>
                    </select>
                </div>
                
                <div class="mhlw-filter-group" id="filter_value_container" style="display: none;">
                    <label for="filter_value"><?php _e('Select:', 'mhlw-compliant-stress-check-system'); ?></label>
                    <select id="filter_value" name="filter_value">
                        <option value=""><?php _e('-- Select --', 'mhlw-compliant-stress-check-system'); ?></option>
                    </select>
                </div>
                
                <button type="button" id="mhlw-generate-analysis" class="button button-primary">
                    <?php _e('Generate Analysis', 'mhlw-compliant-stress-check-system'); ?>
                </button>
            </div>
            
            <div class="mhlw-minimum-notice">
                <p>
                    <strong><?php _e('Note:', 'mhlw-compliant-stress-check-system'); ?></strong>
                    <?php printf(
                        __('Group analysis results are only displayed for organizations with %d or more valid responses to prevent personal identification.', 'mhlw-compliant-stress-check-system'),
                        Mhlw_Stress_Check_Config::get_minimum_group_size()
                    ); ?>
                </p>
            </div>
        </div>
        
        <div id="mhlw-analysis-results" class="mhlw-analysis-results" style="display: none;">
            <h2><?php _e('Analysis Results', 'mhlw-compliant-stress-check-system'); ?></h2>
            
            <div id="mhlw-no-results" class="mhlw-no-results" style="display: none;">
                <div class="notice notice-warning">
                    <p id="mhlw-no-results-message"></p>
                </div>
            </div>
            
            <div id="mhlw-results-content" style="display: none;">
                <div class="mhlw-results-summary">
                    <div class="mhlw-summary-card">
                        <h3><?php _e('Total Respondents', 'mhlw-compliant-stress-check-system'); ?></h3>
                        <div id="mhlw-total-respondents" class="mhlw-summary-value">-</div>
                    </div>
                    <div class="mhlw-summary-card">
                        <h3><?php _e('High-Stress Rate', 'mhlw-compliant-stress-check-system'); ?></h3>
                        <div id="mhlw-high-stress-rate" class="mhlw-summary-value">-</div>
                    </div>
                    <div class="mhlw-summary-card">
                        <h3><?php _e('Domain A Average', 'mhlw-compliant-stress-check-system'); ?></h3>
                        <div id="mhlw-domain-a-avg" class="mhlw-summary-value">-</div>
                    </div>
                    <div class="mhlw-summary-card">
                        <h3><?php _e('Domain B Average', 'mhlw-compliant-stress-check-system'); ?></h3>
                        <div id="mhlw-domain-b-avg" class="mhlw-summary-value">-</div>
                    </div>
                    <div class="mhlw-summary-card">
                        <h3><?php _e('Domain C Average', 'mhlw-compliant-stress-check-system'); ?></h3>
                        <div id="mhlw-domain-c-avg" class="mhlw-summary-value">-</div>
                    </div>
                </div>
                
                <div class="mhlw-chart-section">
                    <h3><?php _e('Score Distribution', 'mhlw-compliant-stress-check-system'); ?></h3>
                    <div class="mhlw-chart-container">
                        <canvas id="mhlwGroupChart"></canvas>
                    </div>
                </div>
                
                <div class="mhlw-export-section">
                    <h3><?php _e('Export Data', 'mhlw-compliant-stress-check-system'); ?></h3>
                    <button type="button" id="mhlw-export-csv" class="button button-secondary">
                        <?php _e('Download CSV', 'mhlw-compliant-stress-check-system'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    var departments = <?php echo json_encode($departments); ?>;
    var orgLevels = <?php echo json_encode($org_levels); ?>;
    var groupChart = null;
    
    // Handle filter type change
    $('#filter_type').on('change', function() {
        var type = $(this).val();
        var $valueContainer = $('#filter_value_container');
        var $valueSelect = $('#filter_value');
        
        $valueSelect.empty().append('<option value=""><?php _e("-- Select --", "mhlw-compliant-stress-check-system"); ?></option>');
        
        if (type === 'company') {
            $valueContainer.hide();
        } else if (type === 'department') {
            $valueContainer.show();
            $.each(departments, function(i, dept) {
                $valueSelect.append('<option value="' + dept.dept_id + '">' + dept.dept_name + ' (' + dept.dept_id + ')</option>');
            });
        } else {
            // Organization level
            $valueContainer.show();
            var levels = orgLevels[type];
            $.each(levels, function(key, value) {
                $valueSelect.append('<option value="' + value + '">' + value + '</option>');
            });
        }
    });
    
    // Generate analysis
    $('#mhlw-generate-analysis').on('click', function() {
        var filterType = $('#filter_type').val();
        var filterValue = $('#filter_value').val();
        
        if (filterType !== 'company' && !filterValue) {
            alert('<?php _e("Please select a filter value.", "mhlw-compliant-stress-check-system"); ?>');
            return;
        }
        
        $('#mhlw-analysis-results').show();
        
        $.ajax({
            url: mhlw_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mhlw_get_group_analysis',
                nonce: mhlw_admin_ajax.nonce,
                filter_type: filterType,
                filter_value: filterValue
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.show_results) {
                        $('#mhlw-no-results').hide();
                        $('#mhlw-results-content').show();
                        
                        // Update statistics
                        var stats = response.data.statistics;
                        $('#mhlw-total-respondents').text(response.data.count);
                        $('#mhlw-high-stress-rate').text(stats.high_stress_percentage + '%');
                        $('#mhlw-domain-a-avg').text(stats.domain_a_average);
                        $('#mhlw-domain-b-avg').text(stats.domain_b_average);
                        $('#mhlw-domain-c-avg').text(stats.domain_c_average);
                        
                        // Update chart
                        updateChart(stats);
                    } else {
                        $('#mhlw-results-content').hide();
                        $('#mhlw-no-results').show();
                        $('#mhlw-no-results-message').text(response.data.message);
                    }
                } else {
                    alert(response.data.message || '<?php _e("An error occurred.", "mhlw-compliant-stress-check-system"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("An error occurred while fetching data.", "mhlw-compliant-stress-check-system"); ?>');
            }
        });
    });
    
    function updateChart(stats) {
        var ctx = document.getElementById('mhlwGroupChart').getContext('2d');
        
        if (groupChart) {
            groupChart.destroy();
        }
        
        groupChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    '<?php _e("Domain A", "mhlw-compliant-stress-check-system"); ?>',
                    '<?php _e("Domain B", "mhlw-compliant-stress-check-system"); ?>',
                    '<?php _e("Domain C", "mhlw-compliant-stress-check-system"); ?>',
                    '<?php _e("High-Stress %", "mhlw-compliant-stress-check-system"); ?>'
                ],
                datasets: [{
                    label: '<?php _e("Average Scores", "mhlw-compliant-stress-check-system"); ?>',
                    data: [
                        stats.domain_a_average,
                        stats.domain_b_average,
                        stats.domain_c_average,
                        stats.high_stress_percentage
                    ],
                    backgroundColor: [
                        'rgba(33, 150, 243, 0.7)',
                        'rgba(244, 67, 54, 0.7)',
                        'rgba(76, 175, 80, 0.7)',
                        'rgba(255, 152, 0, 0.7)'
                    ],
                    borderColor: [
                        'rgba(33, 150, 243, 1)',
                        'rgba(244, 67, 54, 1)',
                        'rgba(76, 175, 80, 1)',
                        'rgba(255, 152, 0, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    // Export CSV
    $('#mhlw-export-csv').on('click', function() {
        // TODO: Implement CSV export functionality
        alert('<?php _e("CSV export functionality will be implemented here.", "mhlw-compliant-stress-check-system"); ?>');
    });
});
</script>

<style>
.mhlw-group-analysis-container {
    max-width: 1000px;
}

.mhlw-filter-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.mhlw-filter-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.mhlw-filter-controls {
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

.mhlw-filter-group select {
    min-width: 200px;
}

.mhlw-minimum-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 15px;
    margin-top: 20px;
}

.mhlw-minimum-notice p {
    margin: 0;
}

.mhlw-analysis-results {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
}

.mhlw-analysis-results h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.mhlw-results-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.mhlw-summary-card {
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.mhlw-summary-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
}

.mhlw-summary-value {
    font-size: 28px;
    font-weight: bold;
    color: #1d2327;
}

.mhlw-chart-section {
    margin: 30px 0;
}

.mhlw-chart-section h3 {
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.mhlw-chart-container {
    max-width: 600px;
    margin: 20px auto;
}

.mhlw-export-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #c3c4c7;
}

.mhlw-no-results {
    margin: 20px 0;
}
</style>
