<?php
/**
 * MHLW Stress Check System Test Script
 * Run this in WordPress admin or via WP-CLI
 */

// Test database connection
echo "Testing database tables...\n";
global $wpdb;

$tables = array(
    'mhlw_departments',
    'mhlw_stress_responses', 
    'mhlw_response_details',
    'mhlw_response_drafts',
    'mhlw_login_attempts'
);

foreach ($tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $result = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($result === $table_name) {
        echo "✓ $table_name exists\n";
    } else {
        echo "✗ $table_name missing\n";
    }
}

// Test configuration
echo "\nTesting configuration...\n";
if (class_exists('Mhlw_Stress_Check_Config')) {
    echo "✓ Config class loaded\n";
    $questions = Mhlw_Stress_Check_Config::get_questions();
    echo "✓ " . count($questions) . " questions loaded\n";
} else {
    echo "✗ Config class not found\n";
}

// Test scoring
echo "\nTesting scoring...\n";
if (class_exists('Mhlw_Stress_Check_Scoring')) {
    echo "✓ Scoring class loaded\n";
    
    // Test with sample data
    $sample_responses = array();
    for ($i = 1; $i <= 57; $i++) {
        $sample_responses[$i] = 1; // All "No" responses
    }
    
    $scores = Mhlw_Stress_Check_Scoring::calculate_scores($sample_responses);
    echo "✓ Scoring calculation works\n";
    echo "  Domain A: " . $scores['domain_a'] . "\n";
    echo "  Domain B: " . $scores['domain_b'] . "\n";
    echo "  Domain C: " . $scores['domain_c'] . "\n";
    echo "  High Stress: " . ($scores['is_high_stress'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Scoring class not found\n";
}

// Test security
echo "\nTesting security...\n";
if (class_exists('Mhlw_Stress_Check_Security')) {
    echo "✓ Security class loaded\n";
} else {
    echo "✗ Security class not found\n";
}

// Test PDF
echo "\nTesting PDF generation...\n";
if (class_exists('Mhlw_Stress_Check_PDF')) {
    echo "✓ PDF class loaded\n";
} else {
    echo "✗ PDF class not found\n";
}

echo "\nTest complete!\n";
?>
