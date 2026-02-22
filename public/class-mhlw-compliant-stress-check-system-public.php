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

}
