<?php

/**
 * Database handler for the stress check system
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_Database {

	/**
	 * Encrypt data using WordPress native encryption
	 *
	 * @since    1.0.0
	 * @param    string    $data    Data to encrypt
	 * @return   string    Encrypted data
	 */
	public static function encrypt($data) {
		// Use WordPress's native encryption if available
		if (function_exists('wp_encrypt')) {
			return wp_encrypt($data);
		}
		
		// Fallback: use base64 with salt (not true encryption but obfuscation)
		$salt = wp_salt('auth');
		return base64_encode($salt . $data);
	}

	/**
	 * Decrypt data using WordPress native encryption
	 *
	 * @since    1.0.0
	 * @param    string    $data    Data to decrypt
	 * @return   string    Decrypted data
	 */
	public static function decrypt($data) {
		// Use WordPress's native decryption if available
		if (function_exists('wp_decrypt')) {
			return wp_decrypt($data);
		}
		
		// Fallback: use base64 with salt removal
		$salt = wp_salt('auth');
		$decoded = base64_decode($data);
		return str_replace($salt, '', $decoded);
	}

	/**
	 * Save or update a draft response
	 *
	 * @since    1.0.0
	 * @param    int       $user_id      User ID
	 * @param    array     $draft_data   Draft response data
	 * @return   bool      True on success, false on failure
	 */
	public static function save_draft($user_id, $draft_data) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_response_drafts';

		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $table WHERE user_id = %d",
			$user_id
		));

		$encrypted_data = self::encrypt(json_encode($draft_data));

		if ($existing) {
			// Update existing draft
			$result = $wpdb->update(
				$table,
				array(
					'draft_data' => $encrypted_data,
					'last_saved_at' => current_time('mysql'),
				),
				array('user_id' => $user_id),
				array('%s', '%s'),
				array('%d')
			);
		} else {
			// Insert new draft
			$result = $wpdb->insert(
				$table,
				array(
					'user_id' => $user_id,
					'draft_data' => $encrypted_data,
				),
				array('%d', '%s')
			);
		}

		return $result !== false;
	}

	/**
	 * Get draft response for a user
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID
	 * @return   array|null  Draft data or null if not found
	 */
	public static function get_draft($user_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_response_drafts';

		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT draft_data FROM $table WHERE user_id = %d",
			$user_id
		));

		if ($row) {
			$decrypted = self::decrypt($row->draft_data);
			return json_decode($decrypted, true);
		}

		return null;
	}

	/**
	 * Delete draft for a user
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID
	 * @return   bool      True on success
	 */
	public static function delete_draft($user_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_response_drafts';

		$wpdb->delete($table, array('user_id' => $user_id), array('%d'));
		return true;
	}

	/**
	 * Save completed stress check response
	 *
	 * @since    1.0.0
	 * @param    int       $user_id      User ID
	 * @param    array     $responses    Question responses
	 * @param    array     $scores       Calculated scores
	 * @return   int|false Response ID on success, false on failure
	 */
	public static function save_response($user_id, $responses, $scores) {
		global $wpdb;
		$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
		$table_details = $wpdb->prefix . 'mhlw_response_details';

		$wpdb->query('START TRANSACTION');

		try {
			// Check if user already has a completed response
			$existing = $wpdb->get_var($wpdb->prepare(
				"SELECT id FROM $table_responses WHERE user_id = %d AND status = 'completed'",
				$user_id
			));

			if ($existing) {
				// Update existing response
				$wpdb->update(
					$table_responses,
					array(
						'domain_a_score' => $scores['domain_scores']['A'],
						'domain_b_score' => $scores['domain_scores']['B'],
						'domain_c_score' => $scores['domain_scores']['C'],
						'is_high_stress' => $scores['is_high_stress'] ? 1 : 0,
						'completed_at' => current_time('mysql'),
						'updated_at' => current_time('mysql'),
					),
					array('id' => $existing),
					array('%d', '%d', '%d', '%d', '%s', '%s'),
					array('%d')
				);
				$response_id = $existing;

				// Delete old details
				$wpdb->delete($table_details, array('response_id' => $existing), array('%d'));
			} else {
				// Insert new response
				$wpdb->insert(
					$table_responses,
					array(
						'user_id' => $user_id,
						'status' => 'completed',
						'domain_a_score' => $scores['domain_scores']['A'],
						'domain_b_score' => $scores['domain_scores']['B'],
						'domain_c_score' => $scores['domain_scores']['C'],
						'is_high_stress' => $scores['is_high_stress'] ? 1 : 0,
						'completed_at' => current_time('mysql'),
					),
					array('%d', '%s', '%d', '%d', '%d', '%d', '%s')
				);
				$response_id = $wpdb->insert_id;
			}

			// Save response details
			foreach ($responses as $question_number => $response_value) {
				$wpdb->insert(
					$table_details,
					array(
						'response_id' => $response_id,
						'question_number' => $question_number,
						'response_value' => $response_value,
					),
					array('%d', '%d', '%d')
				);
			}

			// Delete draft after completion
			self::delete_draft($user_id);

			$wpdb->query('COMMIT');
			return $response_id;

		} catch (Exception $e) {
			$wpdb->query('ROLLBACK');
			return false;
		}
	}

	/**
	 * Get response by ID
	 *
	 * @since    1.0.0
	 * @param    int       $response_id    Response ID
	 * @return   object|null  Response object or null
	 */
	public static function get_response($response_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_stress_responses';

		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE id = %d",
			$response_id
		));
	}

	/**
	 * Get response details by response ID
	 *
	 * @since    1.0.0
	 * @param    int       $response_id    Response ID
	 * @return   array     Array of response details
	 */
	public static function get_response_details($response_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_response_details';

		$results = $wpdb->get_results($wpdb->prepare(
			"SELECT question_number, response_value FROM $table WHERE response_id = %d",
			$response_id
		), ARRAY_A);

		$responses = array();
		foreach ($results as $row) {
			$responses[$row['question_number']] = $row['response_value'];
		}

		return $responses;
	}

	/**
	 * Get user's completed response
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID
	 * @return   object|null  Response object or null
	 */
	public static function get_user_response($user_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_stress_responses';

		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE user_id = %d AND status = 'completed'",
			$user_id
		));
	}

	/**
	 * Import department from CSV
	 *
	 * @since    1.0.0
	 * @param    array     $dept_data    Department data array
	 * @return   bool      True on success
	 */
	public static function import_department($dept_data) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_departments';

		$existing = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $table WHERE dept_id = %s",
			$dept_data['dept_id']
		));

		if ($existing) {
			// Update existing
			$wpdb->update(
				$table,
				array(
					'dept_name' => $dept_data['dept_name'],
					'org_level_1' => $dept_data['org_level_1'],
					'org_level_2' => $dept_data['org_level_2'],
					'org_level_3' => $dept_data['org_level_3'],
				),
				array('dept_id' => $dept_data['dept_id']),
				array('%s', '%s', '%s', '%s'),
				array('%s')
			);
		} else {
			// Insert new
			$wpdb->insert(
				$table,
				array(
					'dept_id' => $dept_data['dept_id'],
					'dept_name' => $dept_data['dept_name'],
					'org_level_1' => $dept_data['org_level_1'],
					'org_level_2' => $dept_data['org_level_2'],
					'org_level_3' => $dept_data['org_level_3'],
				),
				array('%s', '%s', '%s', '%s', '%s')
			);
		}

		return true;
	}

	/**
	 * Get department by ID
	 *
	 * @since    1.0.0
	 * @param    string    $dept_id    Department ID
	 * @return   object|null  Department object or null
	 */
	public static function get_department($dept_id) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_departments';

		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE dept_id = %s",
			$dept_id
		));
	}

	/**
	 * Get all departments
	 *
	 * @since    1.0.0
	 * @return   array     Array of department objects
	 */
	public static function get_all_departments() {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_departments';

		return $wpdb->get_results("SELECT * FROM $table ORDER BY dept_name");
	}

	/**
	 * Log login attempt
	 *
	 * @since    1.0.0
	 * @param    string    $username       Username attempted
	 * @param    string    $ip_address     IP address
	 * @param    bool      $is_successful   Whether login was successful
	 * @return   bool      True on success
	 */
	public static function log_login_attempt($username, $ip_address, $is_successful) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_login_attempts';

		$wpdb->insert(
			$table,
			array(
				'username' => $username,
				'ip_address' => $ip_address,
				'is_successful' => $is_successful ? 1 : 0,
			),
			array('%s', '%s', '%d')
		);

		return true;
	}

	/**
	 * Check if account should be locked due to failed attempts
	 *
	 * @since    1.0.0
	 * @param    string    $username    Username to check
	 * @param    string    $ip_address  IP address to check
	 * @return   bool      True if locked, false otherwise
	 */
	public static function is_account_locked($username, $ip_address) {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_login_attempts';

		$lockout_duration = 30; // minutes
		$max_attempts = 5;

		// Count recent failed attempts
		$failed_count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $table 
			WHERE (username = %s OR ip_address = %s) 
			AND is_successful = 0 
			AND attempt_time > DATE_SUB(NOW(), INTERVAL %d MINUTE)",
			$username,
			$ip_address,
			$lockout_duration
		));

		return $failed_count >= $max_attempts;
	}

	/**
	 * Get responses by department for group analysis
	 *
	 * @since    1.0.0
	 * @param    string    $dept_id    Department ID
	 * @return   array     Array of response objects
	 */
	public static function get_responses_by_department($dept_id) {
		global $wpdb;
		$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
		$table_users = $wpdb->users;
		$table_usermeta = $wpdb->usermeta;

		$sql = $wpdb->prepare(
			"SELECT r.* FROM $table_responses r
			INNER JOIN $table_usermeta um ON um.user_id = r.user_id
			WHERE um.meta_key = 'mhlw_department_id' 
			AND um.meta_value = %s 
			AND r.status = 'completed'",
			$dept_id
		);

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get responses by organization level
	 *
	 * @since    1.0.0
	 * @param    string    $org_level    Organization level (1, 2, or 3)
	 * @param    string    $org_value    Organization value to filter by
	 * @return   array     Array of response objects
	 */
	public static function get_responses_by_organization($org_level, $org_value) {
		global $wpdb;
		$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
		$table_departments = $wpdb->prefix . 'mhlw_departments';
		$table_usermeta = $wpdb->usermeta;

		$org_column = 'org_level_' . intval($org_level);

		$sql = $wpdb->prepare(
			"SELECT r.* FROM $table_responses r
			INNER JOIN $table_usermeta um ON um.user_id = r.user_id
			INNER JOIN $table_departments d ON d.dept_id = um.meta_value
			WHERE um.meta_key = 'mhlw_department_id'
			AND d.$org_column = %s
			AND r.status = 'completed'",
			$org_value
		);

		return $wpdb->get_results($sql, ARRAY_A);
	}

	/**
	 * Get all completed responses (for company-wide analysis)
	 *
	 * @since    1.0.0
	 * @return   array     Array of response objects
	 */
	public static function get_all_completed_responses() {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_stress_responses';

		return $wpdb->get_results(
			"SELECT * FROM $table WHERE status = 'completed'",
			ARRAY_A
		);
	}

	/**
	 * Update department in database
	 *
	 * @since    1.0.0
	 * @param    string    $dept_id        Department ID
	 * @param    string    $dept_name      Department name
	 * @param    string    $org_level_1    Organization level 1
	 * @param    string    $org_level_2    Organization level 2
	 * @param    string    $org_level_3    Organization level 3
	 * @return   int                       Number of rows affected
	 */
	public static function update_department($dept_id, $dept_name, $org_level_1 = '', $org_level_2 = '', $org_level_3 = '') {
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_departments';
		
		// Check if department exists
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT dept_id FROM $table WHERE dept_id = %s",
			$dept_id
		));
		
		if ($exists) {
			// Update existing department
			return $wpdb->update(
				$table,
				array(
					'dept_name' => $dept_name,
					'org_level_1' => $org_level_1,
					'org_level_2' => $org_level_2,
					'org_level_3' => $org_level_3,
					'updated_at' => current_time('mysql')
				),
				array('dept_id' => $dept_id),
				array('%s', '%s', '%s', '%s', '%s'),
				array('%s')
			);
		} else {
			// Insert new department
			return $wpdb->insert(
				$table,
				array(
					'dept_id' => $dept_id,
					'dept_name' => $dept_name,
					'org_level_1' => $org_level_1,
					'org_level_2' => $org_level_2,
					'org_level_3' => $org_level_3,
					'created_at' => current_time('mysql'),
					'updated_at' => current_time('mysql')
				),
				array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
			);
		}
	}

}
