<?php

/**
 * Access Control and Security for the stress check system
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_Security {

	/**
	 * Maximum failed login attempts before lockout
	 *
	 * @since    1.0.0
	 * @var      int
	 */
	const MAX_LOGIN_ATTEMPTS = 5;

	/**
	 * Lockout duration in minutes
	 *
	 * @since    1.0.0
	 * @var      int
	 */
	const LOCKOUT_DURATION = 30;

	/**
	 * Session timeout in minutes
	 *
	 * @since    1.0.0
	 * @var      int
	 */
	const SESSION_TIMEOUT = 30;

	/**
	 * Initialize security hooks
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		// Login attempt limiting
		add_action('wp_login_failed', array(__CLASS__, 'log_failed_login'), 10, 1);
		add_action('wp_login', array(__CLASS__, 'log_successful_login'), 10, 2);
		add_filter('authenticate', array(__CLASS__, 'check_login_lockout'), 30, 3);

		// Session timeout
		add_action('init', array(__CLASS__, 'check_session_timeout'));
		add_action('wp_login', array(__CLASS__, 'set_session_start'), 10, 2);

		// Redirect employees to admin dashboard
		add_action('wp_login', array(__CLASS__, 'redirect_employee_to_admin'), 10, 2);

		// Restrict admin menu for employees
		add_action('admin_menu', array(__CLASS__, 'restrict_admin_menu'), 999);
		add_action('admin_init', array(__CLASS__, 'restrict_admin_access'));

		// Hide admin bar for employees
		add_action('after_setup_theme', array(__CLASS__, 'hide_admin_bar'));

		// Add custom login error messages
		add_filter('login_errors', array(__CLASS__, 'custom_login_errors'));

		// Force HTTPS
		if (!is_ssl()) {
			add_action('template_redirect', array(__CLASS__, 'force_https'));
			add_action('admin_init', array(__CLASS__, 'force_https'));
		}
	}

	/**
	 * Log failed login attempt
	 *
	 * @since    1.0.0
	 * @param    string    $username    Username attempted
	 */
	public static function log_failed_login($username) {
		$ip_address = self::get_client_ip();
		Mhlw_Stress_Check_Database::log_login_attempt($username, $ip_address, false);
	}

	/**
	 * Log successful login
	 *
	 * @since    1.0.0
	 * @param    string    $user_login    Username
	 * @param    WP_User   $user          User object
	 */
	public static function log_successful_login($user_login, $user) {
		$ip_address = self::get_client_ip();
		Mhlw_Stress_Check_Database::log_login_attempt($user_login, $ip_address, true);

		// Clear failed attempts for this user
		global $wpdb;
		$table = $wpdb->prefix . 'mhlw_login_attempts';
		$wpdb->delete($table, array('username' => $user_login, 'is_successful' => 0), array('%s', '%d'));
	}

	/**
	 * Check if account is locked out during authentication
	 *
	 * @since    1.0.0
	 * @param    WP_User|WP_Error    $user       User object or error
	 * @param    string              $username   Username
	 * @param    string              $password   Password
	 * @return   WP_User|WP_Error                User object or error
	 */
	public static function check_login_lockout($user, $username, $password) {
		if (empty($username)) {
			return $user;
		}

		$ip_address = self::get_client_ip();

		if (Mhlw_Stress_Check_Database::is_account_locked($username, $ip_address)) {
			return new WP_Error(
				'account_locked',
				sprintf(
					__('Too many failed login attempts. Please try again after %d minutes.', 'mhlw-compliant-stress-check-system'),
					self::LOCKOUT_DURATION
				)
			);
		}

		return $user;
	}

	/**
	 * Set session start time on login
	 *
	 * @since    1.0.0
	 * @param    string    $user_login    Username
	 * @param    WP_User   $user          User object
	 */
	public static function set_session_start($user_login, $user) {
		update_user_meta($user->ID, 'mhlw_session_start', time());
	}

	/**
	 * Check session timeout
	 *
	 * @since    1.0.0
	 */
	public static function check_session_timeout() {
		if (!is_user_logged_in()) {
			return;
		}

		$user_id = get_current_user_id();
		$session_start = get_user_meta($user_id, 'mhlw_session_start', true);

		if (!$session_start) {
			update_user_meta($user_id, 'mhlw_session_start', time());
			return;
		}

		$elapsed = (time() - $session_start) / 60; // minutes

		if ($elapsed > self::SESSION_TIMEOUT) {
			// Clear session meta
			delete_user_meta($user_id, 'mhlw_session_start');

			// Log out user
			wp_logout();

			// Redirect to login with message
			wp_redirect(wp_login_url() . '?session_expired=1');
			exit;
		}
	}

	/**
	 * Redirect employee to admin dashboard after login
	 *
	 * @since    1.0.0
	 * @param    string    $user_login    Username
	 * @param    WP_User   $user          User object
	 */
	public static function redirect_employee_to_admin($user_login, $user) {
		if (in_array('mhlw_employee', $user->roles)) {
			wp_redirect(admin_url('admin.php?page=mhlw-employee-assessment'));
			exit;
		}
	}

	/**
	 * Restrict admin menu for employees
	 *
	 * @since    1.0.0
	 */
	public static function restrict_admin_menu() {
		$user = wp_get_current_user();
		
		if (!in_array('mhlw_employee', $user->roles)) {
			return;
		}

		// Remove all admin menus except our custom ones
		global $menu;
		$allowed_menus = array();
		
		foreach ($menu as $key => $item) {
			// Keep only our stress check menu
			if (isset($item[5]) && strpos($item[5], 'mhlw-employee-assessment') !== false) {
				$allowed_menus[$key] = $item;
			}
		}
		
		$menu = $allowed_menus;

		// Remove all submenu items except our custom ones
		global $submenu;
		if (isset($submenu['mhlw-employee-assessment'])) {
			// Keep only our submenu items
			$allowed_submenu = array();
			foreach ($submenu['mhlw-employee-assessment'] as $item) {
				if (isset($item[2]) && in_array($item[2], array('mhlw-employee-assessment', 'mhlw-employee-results'))) {
					$allowed_submenu[] = $item;
				}
			}
			$submenu['mhlw-employee-assessment'] = $allowed_submenu;
		}

		// Remove dashboard and other default menus
		remove_menu_page('index.php'); // Dashboard
		remove_menu_page('edit.php'); // Posts
		remove_menu_page('upload.php'); // Media
		remove_menu_page('edit.php?post_type=page'); // Pages
		remove_menu_page('edit-comments.php'); // Comments
		remove_menu_page('themes.php'); // Appearance
		remove_menu_page('plugins.php'); // Plugins
		remove_menu_page('users.php'); // Users
		remove_menu_page('tools.php'); // Tools
		remove_menu_page('options-general.php'); // Settings
	}

	/**
	 * Restrict admin access based on role
	 *
	 * @since    1.0.0
	 */
	public static function restrict_admin_access() {
		if (!is_user_logged_in()) {
			return;
		}

		$user = wp_get_current_user();
		$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

		// Allow administrators and custom admin roles full access
		if (in_array('administrator', $user->roles) ||
			in_array('mhlw_implementation_admin', $user->roles) ||
			in_array('mhlw_general_admin', $user->roles)) {
			return;
		}

		// Employees can only access their specific pages
		if (in_array('mhlw_employee', $user->roles)) {
			$allowed_pages = array('mhlw-employee-assessment', 'mhlw-employee-results');
			
			if (!in_array($current_page, $allowed_pages)) {
				wp_redirect(admin_url('admin.php?page=mhlw-employee-assessment'));
				exit;
			}
			return;
		}

		// Redirect other roles away from admin
		wp_redirect(home_url());
		exit;
	}

	/**
	 * Hide admin bar for employees
	 *
	 * @since    1.0.0
	 */
	public static function hide_admin_bar() {
		if (!is_user_logged_in()) {
			return;
		}

		$user = wp_get_current_user();
		if (in_array('mhlw_employee', $user->roles)) {
			add_filter('show_admin_bar', '__return_false');
		}
	}

	/**
	 * Enforce password strength
	 *
	 * @since    1.0.0
	 * @param    WP_Error    $errors     Error object
	 * @param    boolean     $update     Whether updating
	 * @param    WP_User     $user       User object
	 * @return   WP_Error                Error object
	 */
	public static function enforce_password_strength($errors, $update, $user) {
		if (!$update || empty($_POST['pass1'])) {
			return $errors;
		}

		// Only enforce for our custom roles
		if (!in_array('mhlw_employee', $user->roles) &&
			!in_array('mhlw_implementation_admin', $user->roles) &&
			!in_array('mhlw_general_admin', $user->roles)) {
			return $errors;
		}

		$password = $_POST['pass1'];

		// Check minimum length
		if (strlen($password) < 8) {
			$errors->add('password_too_short', __('Password must be at least 8 characters long.', 'mhlw-compliant-stress-check-system'));
			return $errors;
		}

		// Check complexity (alphanumeric combination recommended)
		if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
			$errors->add('password_not_complex', __('Password must contain both letters and numbers.', 'mhlw-compliant-stress-check-system'));
			return $errors;
		}

		return $errors;
	}

	/**
	 * Custom login error messages
	 *
	 * @since    1.0.0
	 * @param    string    $error    Error message
	 * @return   string              Modified error message
	 */
	public static function custom_login_errors($error) {
		// Don't reveal whether username or password was wrong
		if (strpos($error, 'Invalid username') !== false || strpos($error, 'incorrect password') !== false) {
			return __('Invalid login credentials.', 'mhlw-compliant-stress-check-system');
		}
		return $error;
	}

	/**
	 * Force HTTPS redirect
	 *
	 * @since    1.0.0
	 */
	public static function force_https() {
		if (!is_ssl() && !self::is_localhost()) {
			$redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			wp_redirect($redirect_url, 301);
			exit;
		}
	}

	/**
	 * Check if user has permission to view individual data
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID to check (optional, defaults to current user)
	 * @return   bool                  True if allowed, false otherwise
	 */
	public static function can_view_individual_data($user_id = null) {
		if ($user_id === null) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by('id', $user_id);
		if (!$user) {
			return false;
		}

		return in_array('mhlw_implementation_admin', $user->roles) ||
			   in_array('administrator', $user->roles);
	}

	/**
	 * Check if user has permission to view group analysis
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID to check (optional, defaults to current user)
	 * @return   bool                  True if allowed, false otherwise
	 */
	public static function can_view_group_analysis($user_id = null) {
		if ($user_id === null) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by('id', $user_id);
		if (!$user) {
			return false;
		}

		return in_array('mhlw_implementation_admin', $user->roles) ||
			   in_array('mhlw_general_admin', $user->roles) ||
			   in_array('administrator', $user->roles);
	}

	/**
	 * Check if user can view their own results
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID to check (optional, defaults to current user)
	 * @return   bool                  True if allowed, false otherwise
	 */
	public static function can_view_own_results($user_id = null) {
		if ($user_id === null) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by('id', $user_id);
		if (!$user) {
			return false;
		}

		return in_array('mhlw_employee', $user->roles) ||
			   in_array('mhlw_implementation_admin', $user->roles) ||
			   in_array('administrator', $user->roles);
	}

	/**
	 * Check if user can take stress check
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID to check (optional, defaults to current user)
	 * @return   bool                  True if allowed, false otherwise
	 */
	public static function can_take_stress_check($user_id = null) {
		if ($user_id === null) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by('id', $user_id);
		if (!$user) {
			return false;
		}

		// Check if already completed
		$existing = Mhlw_Stress_Check_Database::get_user_response($user_id);
		if ($existing) {
			return false;
		}

		return in_array('mhlw_employee', $user->roles);
	}

	/**
	 * Get client IP address
	 *
	 * @since    1.0.0
	 * @return   string    IP address
	 */
	public static function get_client_ip() {
		$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
		foreach ($ip_keys as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					$ip = trim($ip);
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
						return $ip;
					}
				}
			}
		}
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	}

	/**
	 * Check if running on localhost
	 *
	 * @since    1.0.0
	 * @return   bool    True if localhost, false otherwise
	 */
	private static function is_localhost() {
		$whitelist = array('127.0.0.1', '::1', 'localhost');
		return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
	}

	/**
	 * Check if minimum group size requirement is met
	 *
	 * @since    1.0.0
	 * @param    int       $count    Number of respondents
	 * @return   bool                True if meets minimum, false otherwise
	 */
	public static function meets_minimum_group_size($count) {
		return $count >= Mhlw_Stress_Check_Config::get_minimum_group_size();
	}

}
