<?php

/**
 * User Profile Fields for MHLW Stress Check System
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_User_Fields {

	/**
	 * Initialize the user fields functionality
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		// Add fields to user profile
		add_action('show_user_profile', array(__CLASS__, 'show_user_fields'));
		add_action('edit_user_profile', array(__CLASS__, 'show_user_fields'));
		
		// Save user fields
		add_action('personal_options_update', array(__CLASS__, 'save_user_fields'));
		add_action('edit_user_profile_update', array(__CLASS__, 'save_user_fields'));
		
		// Add fields to user registration (for admin adding new users)
		add_action('user_new_form', array(__CLASS__, 'add_new_user_fields'));
		add_action('user_register', array(__CLASS__, 'save_new_user_fields'));
		
		// Add custom columns to users list
		add_filter('manage_users_columns', array(__CLASS__, 'add_user_columns'));
		add_action('manage_users_custom_column', array(__CLASS__, 'show_user_columns'), 10, 3);
		
		// Add filters to users list
		add_action('restrict_manage_users', array(__CLASS__, 'add_user_filters'));
		add_filter('pre_get_users', array(__CLASS__, 'filter_users'));
	}

	/**
	 * Display custom fields in user profile
	 *
	 * @since    1.0.0
	 * @param    WP_User    $user    User object
	 */
	public static function show_user_fields($user) {
		// Only show for relevant roles
		$relevant_roles = array('mhlw_employee', 'mhlw_implementation_admin', 'mhlw_general_admin');
		if (!array_intersect($relevant_roles, $user->roles)) {
			return;
		}
		
		$employee_id = get_user_meta($user->ID, 'mhlw_employee_id', true);
		$department_id = get_user_meta($user->ID, 'mhlw_department_id', true);
		$department_name = get_user_meta($user->ID, 'mhlw_department_name', true);
		$org_level_1 = get_user_meta($user->ID, 'mhlw_org_level_1', true);
		$org_level_2 = get_user_meta($user->ID, 'mhlw_org_level_2', true);
		$org_level_3 = get_user_meta($user->ID, 'mhlw_org_level_3', true);
		
		// Get existing departments for dropdown
		$departments = Mhlw_Stress_Check_Database::get_all_departments();
		?>
		<h2><?php _e('MHLW Stress Check Information', 'mhlw-compliant-stress-check-system'); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="mhlw_employee_id"><?php _e('Employee ID', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_employee_id" id="mhlw_employee_id" 
						   value="<?php echo esc_attr($employee_id); ?>" class="regular-text" />
					<p class="description"><?php _e('Unique employee identifier', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_department_id"><?php _e('Department ID', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_department_id" id="mhlw_department_id" 
						   value="<?php echo esc_attr($department_id); ?>" class="regular-text" />
					<p class="description"><?php _e('Unique department identifier', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_department_name"><?php _e('Department Name', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_department_name" id="mhlw_department_name" 
						   value="<?php echo esc_attr($department_name); ?>" class="regular-text" />
					<p class="description"><?php _e('Department display name', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_1"><?php _e('Organization Level 1', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_1" id="mhlw_org_level_1" 
						   value="<?php echo esc_attr($org_level_1); ?>" class="regular-text" />
					<p class="description"><?php _e('Top-level organization (e.g., Division)', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_2"><?php _e('Organization Level 2', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_2" id="mhlw_org_level_2" 
						   value="<?php echo esc_attr($org_level_2); ?>" class="regular-text" />
					<p class="description"><?php _e('Mid-level organization (e.g., Branch)', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_3"><?php _e('Organization Level 3', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_3" id="mhlw_org_level_3" 
						   value="<?php echo esc_attr($org_level_3); ?>" class="regular-text" />
					<p class="description"><?php _e('Lower-level organization (e.g., Section)', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
		</table>
		
		<?php if (in_array('mhlw_employee', $user->roles)) : ?>
			<h3><?php _e('Stress Check Status', 'mhlw-compliant-stress-check-system'); ?></h3>
			<table class="form-table">
				<?php
				$response = Mhlw_Stress_Check_Database::get_user_response($user->ID);
				if ($response) : ?>
					<tr>
						<th><?php _e('Assessment Status', 'mhlw-compliant-stress-check-system'); ?></th>
						<td>
							<span class="mhlw-status-badge mhlw-status-completed">
								<?php _e('Completed', 'mhlw-compliant-stress-check-system'); ?>
							</span>
							<br>
							<em><?php printf(__('Completed on %s', 'mhlw-compliant-stress-check-system'), 
								date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->completed_at))); ?></em>
						</td>
					</tr>
					<tr>
						<th><?php _e('Result', 'mhlw-compliant-stress-check-system'); ?></th>
						<td>
							<?php if ($response->is_high_stress) : ?>
								<span class="mhlw-badge mhlw-badge-high">
									<?php _e('High Stress', 'mhlw-compliant-stress-check-system'); ?>
								</span>
							<?php else : ?>
								<span class="mhlw-badge mhlw-badge-normal">
									<?php _e('Not Applicable', 'mhlw-compliant-stress-check-system'); ?>
								</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php else : ?>
					<tr>
						<th><?php _e('Assessment Status', 'mhlw-compliant-stress-check-system'); ?></th>
						<td>
							<span class="mhlw-status-badge mhlw-status-pending">
								<?php _e('Not Started', 'mhlw-compliant-stress-check-system'); ?>
							</span>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		<?php endif; ?>
		
		<style>
		.mhlw-badge {
			display: inline-block;
			padding: 3px 8px;
			border-radius: 3px;
			font-size: 12px;
			font-weight: 500;
		}
		.mhlw-badge-high {
			background: #fcf0f1;
			color: #d63638;
			border: 1px solid #d63638;
		}
		.mhlw-badge-normal {
			background: #edfaef;
			color: #008a20;
			border: 1px solid #008a20;
		}
		.mhlw-status-badge {
			display: inline-block;
			padding: 4px 10px;
			border-radius: 4px;
			font-weight: 500;
		}
		.mhlw-status-completed {
			background: #edfaef;
			color: #008a20;
		}
		.mhlw-status-pending {
			background: #fff3cd;
			color: #856404;
		}
		</style>
		<?php
	}

	/**
	 * Save custom user fields
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID
	 */
	public static function save_user_fields($user_id) {
		if (!current_user_can('edit_user', $user_id)) {
			return;
		}
		
		// Save fields
		$fields = array(
			'mhlw_employee_id',
			'mhlw_department_id', 
			'mhlw_department_name',
			'mhlw_org_level_1',
			'mhlw_org_level_2',
			'mhlw_org_level_3'
		);
		
		foreach ($fields as $field) {
			if (isset($_POST[$field])) {
				$value = sanitize_text_field($_POST[$field]);
				update_user_meta($user_id, $field, $value);
			}
		}
		
		// Update departments table if needed
		if (isset($_POST['mhlw_department_id']) && isset($_POST['mhlw_department_name'])) {
			$dept_id = sanitize_text_field($_POST['mhlw_department_id']);
			$dept_name = sanitize_text_field($_POST['mhlw_department_name']);
			
			if (!empty($dept_id) && !empty($dept_name)) {
				Mhlw_Stress_Check_Database::update_department($dept_id, $dept_name, 
					sanitize_text_field($_POST['mhlw_org_level_1'] ?? ''),
					sanitize_text_field($_POST['mhlw_org_level_2'] ?? ''),
					sanitize_text_field($_POST['mhlw_org_level_3'] ?? '')
				);
			}
		}
	}

	/**
	 * Add fields to new user form
	 *
	 * @since    1.0.0
	 */
	public static function add_new_user_fields() {
		?>
		<table class="form-table">
			<tr>
				<th><label for="mhlw_employee_id"><?php _e('Employee ID', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_employee_id" id="mhlw_employee_id" class="regular-text" />
					<p class="description"><?php _e('Required for employees', 'mhlw-compliant-stress-check-system'); ?></p>
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_department_id"><?php _e('Department ID', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_department_id" id="mhlw_department_id" class="regular-text" />
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_department_name"><?php _e('Department Name', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_department_name" id="mhlw_department_name" class="regular-text" />
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_1"><?php _e('Organization Level 1', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_1" id="mhlw_org_level_1" class="regular-text" />
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_2"><?php _e('Organization Level 2', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_2" id="mhlw_org_level_2" class="regular-text" />
				</td>
			</tr>
			
			<tr>
				<th><label for="mhlw_org_level_3"><?php _e('Organization Level 3', 'mhlw-compliant-stress-check-system'); ?></label></th>
				<td>
					<input type="text" name="mhlw_org_level_3" id="mhlw_org_level_3" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save new user fields
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    User ID
	 */
	public static function save_new_user_fields($user_id) {
		if (!current_user_can('edit_user', $user_id)) {
			return;
		}
		
		// Save fields
		$fields = array(
			'mhlw_employee_id',
			'mhlw_department_id', 
			'mhlw_department_name',
			'mhlw_org_level_1',
			'mhlw_org_level_2',
			'mhlw_org_level_3'
		);
		
		foreach ($fields as $field) {
			if (isset($_POST[$field])) {
				$value = sanitize_text_field($_POST[$field]);
				update_user_meta($user_id, $field, $value);
			}
		}
		
		// Update departments table
		if (isset($_POST['mhlw_department_id']) && isset($_POST['mhlw_department_name'])) {
			$dept_id = sanitize_text_field($_POST['mhlw_department_id']);
			$dept_name = sanitize_text_field($_POST['mhlw_department_name']);
			
			if (!empty($dept_id) && !empty($dept_name)) {
				Mhlw_Stress_Check_Database::update_department($dept_id, $dept_name, 
					sanitize_text_field($_POST['mhlw_org_level_1'] ?? ''),
					sanitize_text_field($_POST['mhlw_org_level_2'] ?? ''),
					sanitize_text_field($_POST['mhlw_org_level_3'] ?? '')
				);
			}
		}
	}

	/**
	 * Add custom columns to users list
	 *
	 * @since    1.0.0
	 * @param    array     $columns    Existing columns
	 * @return   array                 Modified columns
	 */
	public static function add_user_columns($columns) {
		$columns['mhlw_employee_id'] = __('Employee ID', 'mhlw-compliant-stress-check-system');
		$columns['mhlw_department'] = __('Department', 'mhlw-compliant-stress-check-system');
		$columns['mhlw_status'] = __('Stress Check', 'mhlw-compliant-stress-check-system');
		return $columns;
	}

	/**
	 * Show custom column content
	 *
	 * @since    1.0.0
	 * @param    string    $value      Column value
	 * @param    string    $column     Column name
	 * @param    int       $user_id    User ID
	 * @return   string                Column content
	 */
	public static function show_user_columns($value, $column, $user_id) {
		switch ($column) {
			case 'mhlw_employee_id':
				return get_user_meta($user_id, 'mhlw_employee_id', true) ?: '-';
				
			case 'mhlw_department':
				return get_user_meta($user_id, 'mhlw_department_name', true) ?: '-';
				
			case 'mhlw_status':
				$response = Mhlw_Stress_Check_Database::get_user_response($user_id);
				if ($response) {
					if ($response->is_high_stress) {
						return '<span class="mhlw-badge mhlw-badge-high">' . __('High Stress', 'mhlw-compliant-stress-check-system') . '</span>';
					} else {
						return '<span class="mhlw-badge mhlw-badge-normal">' . __('Normal', 'mhlw-compliant-stress-check-system') . '</span>';
					}
				} else {
					return '<span class="mhlw-status-badge mhlw-status-pending">' . __('Not Started', 'mhlw-compliant-stress-check-system') . '</span>';
				}
		}
		return $value;
	}

	/**
	 * Add filters to users list
	 *
	 * @since    1.0.0
	 */
	public static function add_user_filters() {
		// Department filter
		$departments = Mhlw_Stress_Check_Database::get_all_departments();
		$selected_dept = isset($_GET['mhlw_department']) ? sanitize_text_field($_GET['mhlw_department']) : '';
		
		echo '<label for="mhlw_department_filter">' . __('Department:', 'mhlw-compliant-stress-check-system') . '</label>';
		echo '<select name="mhlw_department" id="mhlw_department_filter">';
		echo '<option value="">' . __('All Departments', 'mhlw-compliant-stress-check-system') . '</option>';
		
		foreach ($departments as $dept) {
			echo '<option value="' . esc_attr($dept->dept_name) . '" ' . selected($selected_dept, $dept->dept_name, false) . '>';
			echo esc_html($dept->dept_name);
			echo '</option>';
		}
		echo '</select>';
		
		// Status filter
		$selected_status = isset($_GET['mhlw_status']) ? sanitize_text_field($_GET['mhlw_status']) : '';
		
		echo '<label for="mhlw_status_filter">' . __('Status:', 'mhlw-compliant-stress-check-system') . '</label>';
		echo '<select name="mhlw_status" id="mhlw_status_filter">';
		echo '<option value="">' . __('All Status', 'mhlw-compliant-stress-check-system') . '</option>';
		echo '<option value="completed" ' . selected($selected_status, 'completed', false) . '>' . __('Completed', 'mhlw-compliant-stress-check-system') . '</option>';
		echo '<option value="not_started" ' . selected($selected_status, 'not_started', false) . '>' . __('Not Started', 'mhlw-compliant-stress-check-system') . '</option>';
		echo '<option value="high_stress" ' . selected($selected_status, 'high_stress', false) . '>' . __('High Stress', 'mhlw-compliant-stress-check-system') . '</option>';
		echo '</select>';
		
		// CSS for filters
		echo '<style>
		#mhlw_department_filter, #mhlw_status_filter {
			margin: 0 10px 10px 0;
		}
		</style>';
	}

	/**
	 * Filter users query
	 *
	 * @since    1.0.0
	 * @param    WP_User_Query    $query    User query
	 */
	public static function filter_users($query) {
		global $pagenow;
		
		if ($pagenow !== 'users.php') {
			return;
		}
		
		$meta_query = array();
		
		// Department filter
		if (!empty($_GET['mhlw_department'])) {
			$meta_query[] = array(
				'key' => 'mhlw_department_name',
				'value' => sanitize_text_field($_GET['mhlw_department']),
				'compare' => '='
			);
		}
		
		// Status filter
		if (!empty($_GET['mhlw_status'])) {
			switch ($_GET['mhlw_status']) {
				case 'completed':
					$meta_query[] = array(
						'key' => 'mhlw_stress_completed',
						'compare' => 'EXISTS'
					);
					break;
				case 'not_started':
					$meta_query[] = array(
						'key' => 'mhlw_stress_completed',
						'compare' => 'NOT EXISTS'
					);
					break;
				case 'high_stress':
					$meta_query[] = array(
						'key' => 'mhlw_stress_completed',
						'compare' => 'EXISTS'
					);
					// This would need additional logic to check high stress status
					break;
			}
		}
		
		if (!empty($meta_query)) {
			$query->set('meta_query', $meta_query);
		}
	}
}
