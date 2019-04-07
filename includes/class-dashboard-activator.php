<?php

/**
 * Fired during plugin activation
 *
 * @since 		1.0.0
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/admin
 */
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/admin
 * @author 		Sergiy Koyev <skoev@hotmail.com>
 */
class Dashboard_Activator {
    /**
	 * Declare custom plugin settings.	 
	 * Flushes rewrite rules afterwards.
     * 
	 * @since 		1.0.0
	 */
	public static function activate() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-dashboard-admin.php';
        flush_rewrite_rules();
    }// activate()

} // class