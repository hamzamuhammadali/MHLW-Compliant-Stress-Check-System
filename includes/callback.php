<?php
error_log("MHLW: Callback file loaded successfully!");

// Add PDF download AJAX action
add_action( 'wp_ajax_mhlw_download_pdf', 'mhlw_handle_pdf_download' );
add_action( 'wp_ajax_nopriv_mhlw_download_pdf', 'mhlw_handle_pdf_download' );

// Add PDF generation AJAX action
add_action( 'wp_ajax_mhlw_generate_pdf', 'mhlw_generate_pdf' );
add_action( 'wp_ajax_nopriv_mhlw_generate_pdf', 'mhlw_generate_pdf' );

function mhlw_generate_pdf() {
    error_log("PDF generation called");
    check_ajax_referer('mhlw_admin_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    $response_id = intval($_POST['response_id']);
    if (!$response_id) {
        wp_send_json_error(array('message' => __('Invalid response ID.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    // Get response and verify ownership
    $response = Mhlw_Stress_Check_Database::get_response($response_id);
    if (!$response) {
        wp_send_json_error(array('message' => __('Response not found.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    $current_user_id = get_current_user_id();
    if ($response->user_id != $current_user_id && 
        !in_array('mhlw_implementation_admin', wp_get_current_user()->roles) &&
        !in_array('administrator', wp_get_current_user()->roles)) {
        wp_send_json_error(array('message' => __('You do not have permission to download this PDF.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    // Generate PDF URL
    $pdf_url = Mhlw_Stress_Check_PDF::generate_temp_url($response_id);
    
    wp_send_json_success(array(
        'pdf_url' => $pdf_url,
        'message' => __('PDF generated successfully.', 'mhlw-compliant-stress-check-system')
    ));
    wp_die();
}

function mhlw_handle_pdf_download() {
    error_log("PDF download handler called");
	if (!isset($_GET['mhlw_pdf_token']) || !isset($_GET['response_id'])) {
		wp_die(__('Invalid download link.', 'mhlw-compliant-stress-check-system'));
	}

	$token = sanitize_text_field($_GET['mhlw_pdf_token']);
	$response_id = intval($_GET['response_id']);

	// Verify token
	$token_data = get_transient('mhlw_pdf_token_' . $token);
	if (!$token_data || $token_data['response_id'] !== $response_id || $token_data['expires'] < time()) {
		wp_die(__('This download link has expired. Please generate a new one from your My Page.', 'mhlw-compliant-stress-check-system'));
	}

	// Don't delete token immediately - let it expire naturally
	// delete_transient('mhlw_pdf_token_' . $token);

	// Output PDF
	Mhlw_Stress_Check_PDF::output_pdf($response_id);

}

add_action( 'wp_ajax_nopriv_mhlw_submit_responses', 'mhlw_submit_responses' );
add_action( 'wp_ajax_mhlw_submit_responses', 'mhlw_submit_responses' );
function mhlw_submit_responses() {
    error_log("Repsonse");
    check_ajax_referer('mhlw_admin_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    $user_id = get_current_user_id();
    $responses = isset($_POST['responses']) ? $_POST['responses'] : array();

    // Validate all 57 questions are answered
    if (count($responses) !== 57) {
        wp_send_json_error(array('message' => __('Please answer all 57 questions before submitting.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    // Sanitize responses
    $sanitized_responses = array();
    foreach ($responses as $question => $value) {
        $question_num = intval($question);
        $response_val = intval($value);
        if ($question_num >= 1 && $question_num <= 57 && $response_val >= 1 && $response_val <= 4) {
            $sanitized_responses[$question_num] = $response_val;
        }
    }

    // Check all questions are answered
    if (count($sanitized_responses) !== 57) {
        wp_send_json_error(array('message' => __('Please answer all 57 questions before submitting.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    // Calculate scores
    $scores = Mhlw_Stress_Check_Scoring::calculate_scores($sanitized_responses);

    // Save completed response
    $response_id = Mhlw_Stress_Check_Database::save_response($user_id, $sanitized_responses, $scores);

    if ($response_id) {
        wp_send_json_success(array(
            'message' => __('Thank you for completing the stress check.', 'mhlw-compliant-stress-check-system'),
            'redirect' => add_query_arg('stress_check_completed', '1', wp_get_referer()),
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to save responses. Please try again.', 'mhlw-compliant-stress-check-system')));
    }
    wp_die();
}
add_action('wp_ajax_nopriv_mhlw_save_draft', 'mhlw_save_draft');
add_action('wp_ajax_mhlw_save_draft', 'mhlw_save_draft');
function mhlw_save_draft() {
    check_ajax_referer('mhlw_admin_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'mhlw-compliant-stress-check-system')));
        wp_die();
    }

    $user_id = get_current_user_id();
    $responses = isset($_POST['responses']) ? $_POST['responses'] : array();

    // Sanitize responses
    $sanitized_responses = array();
    foreach ($responses as $question => $value) {
        $question_num = intval($question);
        $response_val = intval($value);
        if ($question_num >= 1 && $question_num <= 57 && $response_val >= 1 && $response_val <= 4) {
            $sanitized_responses[$question_num] = $response_val;
        }
    }

    // Save draft
    $result = Mhlw_Stress_Check_Database::save_draft($user_id, $sanitized_responses);

    if ($result) {
        wp_send_json_success(array('message' => __('Draft saved successfully.', 'mhlw-compliant-stress-check-system')));
    } else {
        wp_send_json_error(array('message' => __('Failed to save draft.', 'mhlw-compliant-stress-check-system')));
    }
    wp_die();
}

add_action('wp_ajax_mhlw_get_group_analysis', 'mhlw_get_group_analysis');
function mhlw_get_group_analysis() {
    check_ajax_referer('mhlw_admin_nonce', 'nonce');

    if (!current_user_can('mhlw_view_group_analysis')) {
        wp_send_json_error(array('message' => __('Access denied.', 'mhlw-compliant-stress-check-system')));
    }

    $filter_type = isset($_POST['filter_type']) ? sanitize_text_field($_POST['filter_type']) : 'company';
    $filter_value = isset($_POST['filter_value']) ? sanitize_text_field($_POST['filter_value']) : '';

    // Get responses based on filter
    if ($filter_type === 'company') {
        $responses = Mhlw_Stress_Check_Database::get_all_completed_responses();
    } elseif ($filter_type === 'department') {
        $responses = Mhlw_Stress_Check_Database::get_responses_by_department($filter_value);
    } else {
        // Organization level
        $responses = Mhlw_Stress_Check_Database::get_responses_by_organization($filter_type, $filter_value);
    }

    // Check minimum group size
    $minimum_size = Mhlw_Stress_Check_Config::get_minimum_group_size();
    if (count($responses) < $minimum_size) {
        wp_send_json_success(array(
            'show_results' => false,
            'message' => sprintf(
                __('This organization has fewer than %d valid responses; therefore, group analysis results cannot be displayed.', 'mhlw-compliant-stress-check-system'),
                $minimum_size
            ),
            'count' => count($responses),
        ));
    }

    // Calculate group statistics
    $statistics = Mhlw_Stress_Check_Scoring::calculate_group_statistics(array_column($responses, 'id'));

    wp_send_json_success(array(
        'show_results' => true,
        'statistics' => $statistics,
        'count' => count($responses),
    ));
}