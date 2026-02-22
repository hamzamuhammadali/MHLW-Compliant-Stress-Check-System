<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * @since      1.0.0
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side
 * including the stress check form, save/resume functionality, and results display.
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/public
 * @author     Muhammad Ali HAMZA <hamzamuhammadali0@gmail.com>
 */
class Mhlw_Compliant_Stress_Check_System_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name    The name of the plugin.
	 * @param    string    $version      The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mhlw-compliant-stress-check-system-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mhlw-compliant-stress-check-system-public.js', array('jquery'), $this->version, false);

		// Localize script for AJAX
		wp_localize_script($this->plugin_name, 'mhlw_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mhlw_stress_check_nonce'),
		));
	}

	/**
	 * AJAX handler for saving draft responses
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_draft() {
		check_ajax_referer('mhlw_stress_check_nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('Please log in.', 'mhlw-compliant-stress-check-system')));
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
	}

	/**
	 * AJAX handler for submitting completed responses
	 *
	 * @since    1.0.0
	 */
	public function ajax_submit_responses() {
		check_ajax_referer('mhlw_stress_check_nonce', 'nonce');

		if (!is_user_logged_in()) {
			wp_send_json_error(array('message' => __('Please log in.', 'mhlw-compliant-stress-check-system')));
		}

		$user_id = get_current_user_id();
		$responses = isset($_POST['responses']) ? $_POST['responses'] : array();

		// Validate all 57 questions are answered
		if (count($responses) !== 57) {
			wp_send_json_error(array('message' => __('Please answer all 57 questions before submitting.', 'mhlw-compliant-stress-check-system')));
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
	}

	/**
	 * Register AJAX actions
	 *
	 * @since    1.0.0
	 */
	public function register_ajax_actions() {
		add_action('wp_ajax_mhlw_save_draft', array($this, 'ajax_save_draft'));
		add_action('wp_ajax_mhlw_submit_responses', array($this, 'ajax_submit_responses'));
	}

}
