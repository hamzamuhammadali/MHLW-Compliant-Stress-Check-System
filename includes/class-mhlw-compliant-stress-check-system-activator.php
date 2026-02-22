<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * @since      1.0.0
 *
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 * @author     Muhammad Ali HAMZA <hamzamuhammadali0@gmail.com>
 */
class Mhlw_Compliant_Stress_Check_System_Activator {

	/**
	 * Create database tables and set up custom roles on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::setup_roles();
		self::flush_rewrite_rules();
	}

	/**
	 * Create custom database tables for the stress check system.
	 *
	 * @since    1.0.0
	 */
	private static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Departments table for organizational hierarchy
		$table_departments = $wpdb->prefix . 'mhlw_departments';
		$sql_departments = "CREATE TABLE IF NOT EXISTS $table_departments (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			dept_id varchar(50) NOT NULL,
			dept_name varchar(255) NOT NULL,
			org_level_1 varchar(255) DEFAULT NULL,
			org_level_2 varchar(255) DEFAULT NULL,
			org_level_3 varchar(255) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY dept_id (dept_id)
		) $charset_collate;";

		// Stress check responses table
		$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
		$sql_responses = "CREATE TABLE IF NOT EXISTS $table_responses (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			status enum('draft','completed') DEFAULT 'draft',
			domain_a_score int(11) DEFAULT NULL,
			domain_b_score int(11) DEFAULT NULL,
			domain_c_score int(11) DEFAULT NULL,
			is_high_stress tinyint(1) DEFAULT 0,
			completed_at datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY status (status)
		) $charset_collate;";

		// Response details table (encrypted individual responses)
		$table_response_details = $wpdb->prefix . 'mhlw_response_details';
		$sql_response_details = "CREATE TABLE IF NOT EXISTS $table_response_details (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			response_id bigint(20) unsigned NOT NULL,
			question_number int(11) NOT NULL,
			response_value int(11) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY response_id (response_id),
			KEY question_number (question_number)
		) $charset_collate;";

		// Draft responses table for save-and-resume functionality
		$table_drafts = $wpdb->prefix . 'mhlw_response_drafts';
		$sql_drafts = "CREATE TABLE IF NOT EXISTS $table_drafts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			draft_data longtext NOT NULL,
			last_saved_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_id (user_id)
		) $charset_collate;";

		// Login attempts table for security
		$table_login_attempts = $wpdb->prefix . 'mhlw_login_attempts';
		$sql_login_attempts = "CREATE TABLE IF NOT EXISTS $table_login_attempts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			username varchar(100) NOT NULL,
			ip_address varchar(100) NOT NULL,
			attempt_time datetime DEFAULT CURRENT_TIMESTAMP,
			is_successful tinyint(1) DEFAULT 0,
			PRIMARY KEY (id),
			KEY username (username),
			KEY ip_address (ip_address),
			KEY attempt_time (attempt_time)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_departments);
		dbDelta($sql_responses);
		dbDelta($sql_response_details);
		dbDelta($sql_drafts);
		dbDelta($sql_login_attempts);
	}

	/**
	 * Setup custom WordPress roles for the stress check system.
	 *
	 * @since    1.0.0
	 */
	private static function setup_roles() {
		// Implementation Administrator - Industrial Physician/Designated Personnel
		add_role(
			'mhlw_implementation_admin',
			__('Administration - Industrial Physician/Designated Personnel', 'mhlw-compliant-stress-check-system'),
			array(
				'read' => true,
				'mhlw_view_individual_responses' => true,
				'mhlw_view_individual_results' => true,
				'mhlw_download_individual_pdfs' => true,
				'mhlw_provide_followup' => true,
				'mhlw_view_group_analysis' => true,
				'mhlw_view_response_progress' => true,
				'mhlw_import_employees' => true,
			)
		);

		// General Administrator - Department Manager
		add_role(
			'mhlw_general_admin',
			__('General Administration - Department Manager', 'mhlw-compliant-stress-check-system'),
			array(
				'read' => true,
				'mhlw_view_group_analysis' => true,
				'mhlw_view_response_progress' => true,
				'mhlw_import_employees' => true,
			)
		);

		// Employee role for stress check participants
		add_role(
			'mhlw_employee',
			__('Stress Check Employee', 'mhlw-compliant-stress-check-system'),
			array(
				'read' => true,
				'mhlw_take_stress_check' => true,
				'mhlw_view_own_results' => true,
				'mhlw_download_own_pdf' => true,
			)
		);
	}

	/**
	 * Flush WordPress rewrite rules after plugin activation
	 *
	 * @since    1.0.0
	 */
	private static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

}
