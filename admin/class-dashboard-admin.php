<?php

/**
 * The Spruce beer dashboard plugin specific functionality of the plugin.
 *
 * @since 		1.0.0
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/admin
 */
/**
 * The Spruce beer dashboard plugin specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/admin
 * @author 		Sergiy Koyev <skoev@hotmail.com>
 */
class Dashboard_Admin {
    private static $initiated = false;

    public static function init() {
		if ( !self::$initiated ) {
            self::init_hooks();
        }
    }    
    
    /**
	 * Init hooks
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public static function init_hooks() {
        self::$initiated = true;
        add_action( 'admin_menu', array( 'Dashboard_Admin', 'admin_page' ));
    }

    public static function admin_page() {
        add_menu_page( 'Bruce Beer Dashboard', 'Bruce Beer Dashboard', 'manage_options', __FILE__, array( 'Dashboard_Admin', 'dashboard_admin_page' ), 'dashicons-tickets', 6  );                
    }

    /**
     * Output plugin desscription and details as part of the admin menu for user convinience.
     */
    public static function dashboard_admin_page(){
        $out = '<h3>Spruce Beer Dashboard Plugin</h3>';
        $out .= '<div>Description: <b>By adding a shortcode [spruce_beer_dashboard] it allows to add into webpage a dashboard of the Spruce Beer recent reviews lists the most recent reviews for Spruce Beer.</b></div>';
        $out .= '<div>Shortcode Example: <b>[spruce_beer_dashboard server_url="https://api.untappd.com/v4/beer/checkins" bid="110569" brewery_id="1473" item_count="10" client_id="clientID123" client_secret="clientsecretID123"]Spruce Beer Dashboard[/spruce_beer_dashboard]</b></div>';
        $out .= '<div>Author: <b>Sergiy Koyev</b></div>';
        $out .= '<div>Version: <b>1.0</b></div>';
        $out .= '<div>Date: <b>March 30, 2019</b></div>';
        echo $out;
    }
}