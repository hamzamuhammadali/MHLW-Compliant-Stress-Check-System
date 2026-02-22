<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * @since      1.0.0
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, admin menus, CSV import,
 * group analysis, and role-based dashboard functionality.
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/admin
 * @author     Muhammad Ali HAMZA <hamzamuhammadali0@gmail.com>
 */
class Mhlw_Compliant_Stress_Check_System_Admin {

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
	 * @param    string    $plugin_name    The name of this plugin.
	 * @param    string    $version      The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mhlw-compliant-stress-check-system-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mhlw-compliant-stress-check-system-admin.js', array('jquery'), $this->version, false);

		// Localize script for AJAX
		wp_localize_script($this->plugin_name, 'mhlw_admin_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mhlw_admin_nonce'),
		));
	}

	/**
	 * Register admin menus
	 *
	 * @since    1.0.0
	 */
	public function register_admin_menus() {
		// Main admin menu for administrators
		add_menu_page(
			__('Stress Check System', 'mhlw-compliant-stress-check-system'),
			__('Stress Check', 'mhlw-compliant-stress-check-system'),
			'mhlw_view_group_analysis',
			'mhlw-dashboard',
			array($this, 'render_dashboard'),
			'dashicons-heart',
			30
		);

		// Admin subpages
		add_submenu_page(
			'mhlw-dashboard',
			__('Dashboard', 'mhlw-compliant-stress-check-system'),
			__('Dashboard', 'mhlw-compliant-stress-check-system'),
			'mhlw_view_group_analysis',
			'mhlw-dashboard',
			array($this, 'render_dashboard')
		);

		add_submenu_page(
			'mhlw-dashboard',
			__('Import Employees', 'mhlw-compliant-stress-check-system'),
			__('Import Employees', 'mhlw-compliant-stress-check-system'),
			'mhlw_import_employees',
			'mhlw-import-employees',
			array($this, 'render_import_employees')
		);

		add_submenu_page(
			'mhlw-dashboard',
			__('Group Analysis', 'mhlw-compliant-stress-check-system'),
			__('Group Analysis', 'mhlw-compliant-stress-check-system'),
			'mhlw_view_group_analysis',
			'mhlw-group-analysis',
			array($this, 'render_group_analysis')
		);

		add_submenu_page(
			'mhlw-dashboard',
			__('Individual Results', 'mhlw-compliant-stress-check-system'),
			__('Individual Results', 'mhlw-compliant-stress-check-system'),
			'mhlw_view_individual_results',
			'mhlw-individual-results',
			array($this, 'render_individual_results')
		);

		// Employee dashboard pages (restricted to employee role)
		add_menu_page(
			__('Stress Check Assessment', 'mhlw-compliant-stress-check-system'),
			__('Stress Check', 'mhlw-compliant-stress-check-system'),
			'mhlw_take_stress_check',
			'mhlw-employee-assessment',
			array($this, 'render_employee_assessment'),
			'dashicons-clipboard',
			31
		);

		add_submenu_page(
			'mhlw-employee-assessment',
			__('Results', 'mhlw-compliant-stress-check-system'),
			__('Results', 'mhlw-compliant-stress-check-system'),
			'mhlw_take_stress_check',
			'mhlw-employee-results',
			array($this, 'render_employee_results')
		);
	}

	/**
	 * Render the dashboard page
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard() {
		if (!current_user_can('mhlw_view_response_progress')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-admin-dashboard.php';
	}

	/**
	 * Render the employee assessment page
	 *
	 * @since    1.0.0
	 */
	public function render_employee_assessment() {
		if (!current_user_can('mhlw_take_stress_check')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-employee-dashboard.php';
	}

	/**
	 * Render the employee results page
	 *
	 * @since    1.0.0
	 */
	public function render_employee_results() {
		if (!current_user_can('mhlw_take_stress_check')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-employee-results.php';
	}

	/**
	 * Render the import employees page
	 *
	 * @since    1.0.0
	 */
	public function render_import_employees() {
		if (!current_user_can('mhlw_import_employees')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		// Handle CSV upload
		if (isset($_POST['mhlw_import_nonce']) && wp_verify_nonce($_POST['mhlw_import_nonce'], 'mhlw_import_employees')) {
			$this->handle_csv_import();
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-admin-import.php';
	}

	/**
	 * Handle CSV import
	 *
	 * @since    1.0.0
	 */
	private function handle_csv_import() {
		if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
			add_settings_error(
				'mhlw_import',
				'csv_error',
				__('Error uploading CSV file.', 'mhlw-compliant-stress-check-system'),
				'error'
			);
			return;
		}

		$file = $_FILES['csv_file']['tmp_name'];
		$handle = fopen($file, 'r');

		if (!$handle) {
			add_settings_error(
				'mhlw_import',
				'csv_error',
				__('Error reading CSV file.', 'mhlw-compliant-stress-check-system'),
				'error'
			);
			return;
		}

		// Skip header row
		$header = fgetcsv($handle);

		$imported_count = 0;
		$error_count = 0;
		$errors = array();

		while (($data = fgetcsv($handle)) !== false) {
			// Expected CSV format: Employee ID, Name, Department ID, Department Name, Org Level 1, Org Level 2, Org Level 3
			if (count($data) < 7) {
				$error_count++;
				continue;
			}

			$employee_id = sanitize_text_field($data[0]);
			$name = sanitize_text_field($data[1]);
			$dept_id = sanitize_text_field($data[2]);
			$dept_name = sanitize_text_field($data[3]);
			$org_level_1 = sanitize_text_field($data[4]);
			$org_level_2 = sanitize_text_field($data[5]);
			$org_level_3 = sanitize_text_field($data[6]);

			// Validate required fields
			if (empty($employee_id) || empty($dept_id)) {
				$error_count++;
				$errors[] = sprintf(__('Row %d: Missing required fields', 'mhlw-compliant-stress-check-system'), $imported_count + $error_count + 1);
				continue;
			}

			// Check for duplicate employee ID
			$existing_user = get_users(array(
				'meta_key' => 'mhlw_employee_id',
				'meta_value' => $employee_id,
				'number' => 1,
			));

			if (!empty($existing_user)) {
				// Update existing user
				$user_id = $existing_user[0]->ID;
				wp_update_user(array(
					'ID' => $user_id,
					'display_name' => $name,
				));
			} else {
				// Create new user
				$username = 'mhlw_' . sanitize_user($employee_id);
				$user_id = wp_insert_user(array(
					'user_login' => $username,
					'user_pass' => wp_generate_password(),
					'user_email' => $username . '@stresscheck.local',
					'display_name' => $name,
					'role' => 'mhlw_employee',
				));

				if (is_wp_error($user_id)) {
					$error_count++;
					$errors[] = sprintf(__('Row %d: Error creating user - %s', 'mhlw-compliant-stress-check-system'), $imported_count + $error_count + 1, $user_id->get_error_message());
					continue;
				}
			}

			// Update user meta
			update_user_meta($user_id, 'mhlw_employee_id', $employee_id);
			update_user_meta($user_id, 'mhlw_department_id', $dept_id);
			update_user_meta($user_id, 'mhlw_department_name', $dept_name);
			update_user_meta($user_id, 'mhlw_org_level_1', $org_level_1);
			update_user_meta($user_id, 'mhlw_org_level_2', $org_level_2);
			update_user_meta($user_id, 'mhlw_org_level_3', $org_level_3);

			// Import department
			Mhlw_Stress_Check_Database::import_department(array(
				'dept_id' => $dept_id,
				'dept_name' => $dept_name,
				'org_level_1' => $org_level_1,
				'org_level_2' => $org_level_2,
				'org_level_3' => $org_level_3,
			));

			$imported_count++;
		}

		fclose($handle);

		// Store import results
		set_transient('mhlw_import_results', array(
			'imported' => $imported_count,
			'errors' => $error_count,
			'error_messages' => $errors,
		), 60);

		add_settings_error(
			'mhlw_import',
			'csv_success',
			sprintf(__('Import complete. %d employees imported, %d errors.', 'mhlw-compliant-stress-check-system'), $imported_count, $error_count),
			$error_count > 0 ? 'warning' : 'success'
		);
	}

	/**
	 * Render the group analysis page
	 *
	 * @since    1.0.0
	 */
	public function render_group_analysis() {
		if (!current_user_can('mhlw_view_group_analysis')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-admin-group-analysis.php';
	}

	/**
	 * Render the individual results page
	 *
	 * @since    1.0.0
	 */
	public function render_individual_results() {
		if (!current_user_can('mhlw_view_individual_results')) {
			wp_die(__('You do not have permission to access this page.', 'mhlw-compliant-stress-check-system'));
		}

		include plugin_dir_path(__FILE__) . 'partials/mhlw-admin-individual-results.php';
	}

	/**
	 * AJAX handler for getting group analysis data
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_group_analysis() {
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

	/**
	 * Register AJAX actions for admin
	 *
	 * @since    1.0.0
	 */
	public function register_admin_ajax() {
		add_action('wp_ajax_mhlw_get_group_analysis', array($this, 'ajax_get_group_analysis'));
	}

}
