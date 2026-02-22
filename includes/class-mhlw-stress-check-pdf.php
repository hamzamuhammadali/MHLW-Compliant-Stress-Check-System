<?php

/**
 * PDF Generation for the stress check system
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_PDF {

	/**
	 * Generate PDF for individual results
	 *
	 * @since    1.0.0
	 * @param    int       $response_id    Response ID
	 * @param    int       $user_id        User ID (for verification)
	 * @return   string|WP_Error           PDF content or error
	 */
	public static function generate_individual_pdf($response_id, $user_id = null) {
		// Verify access permissions
		if ($user_id === null) {
			$user_id = get_current_user_id();
		}

		// Get response
		$response = Mhlw_Stress_Check_Database::get_response($response_id);
		if (!$response) {
			return new WP_Error('response_not_found', __('Response not found.', 'mhlw-compliant-stress-check-system'));
		}

		// Check permissions
		$current_user = wp_get_current_user();
		if ($response->user_id != $user_id &&
			!in_array('mhlw_implementation_admin', $current_user->roles) &&
			!in_array('administrator', $current_user->roles)) {
			return new WP_Error('access_denied', __('You do not have permission to view this PDF.', 'mhlw-compliant-stress-check-system'));
		}

		// Get user data
		$user = get_user_by('id', $response->user_id);
		if (!$user) {
			return new WP_Error('user_not_found', __('User not found.', 'mhlw-compliant-stress-check-system'));
		}

		// Get response details and calculate scores
		$response_details = Mhlw_Stress_Check_Database::get_response_details($response_id);
		$scores = Mhlw_Stress_Check_Scoring::calculate_scores($response_details);

		// Get user meta
		$employee_id = get_user_meta($response->user_id, 'mhlw_employee_id', true);
		$department = get_user_meta($response->user_id, 'mhlw_department_name', true);

		// Build PDF content (HTML format for browser print/save as PDF)
		ob_start();
		include plugin_dir_path(dirname(__FILE__)) . 'templates/pdf-individual-result.php';
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Output PDF for download
	 *
	 * @since    1.0.0
	 * @param    int       $response_id    Response ID
	 */
	public static function output_pdf($response_id) {
		error_log("PDF output called for response ID: " . $response_id);
		$user_id = get_current_user_id();

		// Check if user can download their own PDF
		if (!Mhlw_Stress_Check_Security::can_view_own_results($user_id)) {
			wp_die(__('You do not have permission to download this PDF.', 'mhlw-compliant-stress-check-system'));
		}

		// Get response
		$response = Mhlw_Stress_Check_Database::get_response($response_id);
		if (!$response) {
			wp_die(__('Response not found.', 'mhlw-compliant-stress-check-system'));
		}

		// Verify user owns this response or is implementation admin
		$current_user = wp_get_current_user();
		if ($response->user_id != $user_id &&
			!in_array('mhlw_implementation_admin', $current_user->roles) &&
			!in_array('administrator', $current_user->roles)) {
			wp_die(__('You do not have permission to download this PDF.', 'mhlw-compliant-stress-check-system'));
		}

		// Generate PDF content
		$html = self::generate_individual_pdf($response_id, $user_id);
		if (is_wp_error($html)) {
			wp_die($html->get_error_message());
		}

		// Set headers for HTML output (print-friendly)
		header('Content-Type: text/html; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		echo $html;
		exit;
	}

	/**
	 * Generate temporary download URL
	 *
	 * @since    1.0.0
	 * @param    int       $response_id    Response ID
	 * @param    int       $expiration     Expiration time in seconds (default: 1 hour)
	 * @return   string                     Temporary URL
	 */
	public static function generate_temp_url($response_id, $expiration = 3600) {
		$token = wp_create_nonce('mhlw_pdf_' . $response_id . '_' . time());

		// Store token with expiration
		set_transient('mhlw_pdf_token_' . $token, array(
			'response_id' => $response_id,
			'expires' => time() + $expiration,
		), $expiration);

		return add_query_arg(array(
			'action' => 'mhlw_download_pdf',
			'mhlw_pdf_token' => $token,
			'response_id' => $response_id,
		), admin_url('admin-ajax.php'));
	}

	/**
	 * Verify and serve PDF from temporary URL
	 *
	 * @since    1.0.0
	 */
	public static function handle_temp_download() {
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

		// Delete token (one-time use)
		delete_transient('mhlw_pdf_token_' . $token);

		// Output PDF
		self::output_pdf($response_id);
	}

}
