<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * @since             1.0.0
 * @package           Mhlw_Compliant_Stress_Check_System
 *
 * @wordpress-plugin
 * Plugin Name:       MHLW–Compliant Stress Check System
 * Plugin URI:        https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3/
 * Description:       A stress check system, fully compliant with the “Stress Check System based on the Industrial Safety and Health Act” established by the Ministry of Health, Labour and Welfare (MHLW), for internal corporate use
 * Version:           1.0.0
 * Author:            Muhammad Ali HAMZA
 * Author URI:        https://https://www.linkedin.com/in/muhammad-ali-hamza-0b71281a3//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mhlw-compliant-stress-check-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MHLW_COMPLIANT_STRESS_CHECK_SYSTEM_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mhlw-compliant-stress-check-system-activator.php
 */
function activate_mhlw_compliant_stress_check_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mhlw-compliant-stress-check-system-activator.php';
	Mhlw_Compliant_Stress_Check_System_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mhlw-compliant-stress-check-system-deactivator.php
 */
function deactivate_mhlw_compliant_stress_check_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mhlw-compliant-stress-check-system-deactivator.php';
	Mhlw_Compliant_Stress_Check_System_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mhlw_compliant_stress_check_system' );
register_deactivation_hook( __FILE__, 'deactivate_mhlw_compliant_stress_check_system' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mhlw-compliant-stress-check-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mhlw_compliant_stress_check_system() {

	$plugin = new Mhlw_Compliant_Stress_Check_System();
	$plugin->run();

}
run_mhlw_compliant_stress_check_system();
