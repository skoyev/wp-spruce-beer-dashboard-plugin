<?php
/**
* Plugin Name: Spruce Beer Dashboard Plugin
* Plugin URI: https://untappd.com/b/garrison-brewing-company-spruce-beer/110569
* Description: Dasboard webpage that lists the 10 most recent reviews for Spruce Beer. Short code example: 
*    [spruce_beer_dashboard server_url="https://api.untappd.com/v4/beer/checkins"
*                           bid="110569"
*                           brewery_id="1473" 
*                           item_count="10" 
*                           client_id="clientID123" 
*                           client_secret="clientsecretID123"]Spruce Beer Dashboard[/spruce_beer_dashboard]
* Version: 1.0
* Author: Sergiy Koyev
**/

if ( !function_exists( 'add_action' ) ) {
	echo 'Error. It can not be called directly.';
	exit;
}

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('admin_menu', 'dashboard_admin_menu');

/**
 * Admin menu hook to add a new item.
 */
function dashboard_admin_menu(){
    add_management_page('Dashboard', 'Dashboard', 'manage_options', __FILE__, 'dashboard_admin_page');
}

/**
 * Output plugin desscription and details as part of the admin menu for user convinience.
 */
function dashboard_admin_page(){
    $out = '<h3>Spruce Beer Dashboard Plugin</h3>';
    $out .= '<div>Description: <b>By adding a shortcode [spruce_beer_dashboard] it allows to add into webpage a dashboard of the Spruce Beer recent reviews lists the most recent reviews for Spruce Beer.</b></div>';
    $out .= '<div>Shortcode Example: <b>[spruce_beer_dashboard server_url="https://api.untappd.com/v4/beer/checkins" bid="110569" brewery_id="1473" item_count="10" client_id="clientID123" client_secret="clientsecretID123"]Spruce Beer Dashboard[/spruce_beer_dashboard]</b></div>';
    $out .= '<div>Author: <b>Sergiy Koyev</b></div>';
    $out .= '<div>Version: <b>1.0</b></div>';
    $out .= '<div>Date: <b>March 30, 2019</b></div>';
    echo $out;
}

/**
 * Add Dashboard Header section. Fetch Brewery Info for the Header Section
 * From API: https://untappd.com/api/docs#breweryinfo
 */
function dashboard_review_header(&$out, $server_url, $client_id, $client_secret, $brewery_id){
    $fullServerURL = "{$server_url}/brewery/info";
    
    // BID url request param
    if( empty($brewery_id) ){
        log_error('Error. Brewery ID is null. No Dashboard header content is generated.');
        return;    
    }

    $fullServerURL .= "/{$brewery_id}";

    // client_id url request param
    $fullServerURL .= "?client_id={$client_id}&client_secret={$client_secret}";

    $request = wp_remote_get($fullServerURL);

    // check if any wp http request errors                
    if( is_wp_error( $request ) ) {
        return false; 
    }                

    $body     = wp_remote_retrieve_body( $request );
    $arrData  = json_decode($body, true);        
    $brewery  = $arrData['response']['brewery'];        

    $out .= '<div class="dashboard-header">';    
    $out .= "<div><span class='inline-content'>Brewery Picture:</span><img width='80' src='{$brewery['brewery_label']}'/></div>";
    $out .= "<div>Brewery Name: <b>{$brewery['brewery_name']}</b> </div>";
    $out .= "<div>Brewery Country: <b>{$brewery['country_name']}</b> </div>";
    $out .= "<div>Brewery Beer Total: <b>{$brewery['beer_count']}</b> </div>";
    $out .= '</div>';
}

/**
 * Add Dasboard Content section. Fetch Beer Reviews from the API:
 * https://untappd.com/api/docs#beeractivityfeed
 */
function dashboard_review_content(&$out, $server_url, $client_id, $client_secret, $bid, $item_count) {
    $fullServerURL = "{$server_url}/beer/checkins";
    
    // BID url request param
    if( !empty($bid) ){
        $fullServerURL .= "/{$bid}";
    }
    
    // client_id url request param
    $fullServerURL .= "?client_id={$client_id}&client_secret={$client_secret}";

    // items count fetched
    if( !empty($item_count) ){
        $fullServerURL .= (empty($client_id) ? '?' : '&') . "limit={$item_count}";
    }

    if(!empty($fullServerURL)) {
        $request = wp_remote_get($fullServerURL);

        // check if any wp http request errors                
        if( is_wp_error( $request ) ) {
	        return false; 
        }                

        $body    = wp_remote_retrieve_body( $request );
        $arrData = json_decode($body, true);        
        $items   = $arrData['response']['checkins']['items'];        
        $out    .= "<span>Total Most Recent Reviews: <b>" .count($items). "</b></span>";

        foreach( $items as $key => $item ) {
            $brewery = $item['brewery'];
            $beer    = $item['beer'];

            $out .= '<div class="dashboard-header">';

            // rating date            
            $out .= "<div>Rating Date: <b>{$item['created_at']}</b></div>";

            // rating comment      
            $out .= "<div>Rating Comment: <b>{$item['checkin_comment']}</b></div>";                        

            // review score         
            $out .= "<div class='bottom-line'>Rating: <b>{$item['rating_score']}</b> </div>";

            // brewery logo            
            $out .= "<div><span class='inline-content'>Brewery Logo</span><img width='80' src='{$brewery['brewery_label']}'/></div>";

            // brewery name            
            $out .= "<div>Brewery Name: <b>{$brewery['brewery_name']}</b> </div>";

            // beer name         
            $out .= "<div>Beer Name: <b>{$beer['beer_name']}</b> </div>";

            // beer style         
            $out .= "<div>Beer Style: <b>{$beer['beer_style']}</b></div>";

            // alcohol content        
            $out .= "<div>Alcohol Content: <b>{$beer['beer_abv']}</b> </div>";

            // bitterness        
            $out .= "<div>Bitterness(IBU): <b>{$beer['beer_ibu']}</b> </div>";

            // Average Rating (out of 5)        
            $out .= "<div>Average Rating (out of 5): <b>{$item['rating_score']}</b> </div>";

            // beer picture        
            $out .= "<div><span class='inline-content'>Beer :</span> <img width='80' src='{$beer['beer_label']}'/></div>";

            $out .= '</div>';
        }              
    } else {
        log_error('Error. Full Server URL is null.');
    }
}

/**
 * Log error function for writing error log
 */
function log_error($message) {
    if ( WP_DEBUG === true ) {
        if ( is_array($message) || is_object($message) ) {
            error_log( print_r($message, true) );
        } else {
            error_log( $message );
        }
    }
}

/**
 * Function which implements spruce_beer_dashboard short code. It displays Brewer Data in header and
 * most recent #item_count beer reviews.
 */
function spruce_beer_dashboard_creation($atts, $content = null) {
    if(empty($atts)) {
        log_error('Error. Attributes is null.');
        return;
    }

    extract( shortcode_atts(array(
        'server_url' => 'server_url',  // base server URL
        'client_id'  => 'client_id',   // client request ID
        'client_secret' => 'client_secret', // client secret
        'brewery_id' => 'brewery_id',  // brewery_id
        'bid' => 'bid',                // beer id
        'item_count' => 'item_count'   // item count, optional
    ), $atts ) );

    if( empty($server_url) || 
            empty($client_id) || 
                empty($client_secret)){
        log_error('Error. Server URL, Client ID or Client Secret is null.');
        return;
    }

    // secure output by executing the_content filter hook on $content
    $out = !empty($content) ? '<h3>'.apply_filters('the_content', $content).'</h3>' : '';
    // dashboard header
    dashboard_review_header($out, $server_url, $client_id, $client_secret, $brewery_id);
    // dashboard content
    dashboard_review_content($out, $server_url, $client_id, $client_secret, $bid, $item_count);

    return $out;
}

/**
 * Shortcode init function hook.
 */
function dashboard_shortcodes_init(){
    add_shortcode('spruce_beer_dashboard', 'spruce_beer_dashboard_creation');
}

/**
 * Register custom style files.
 */
function register_dasboard_scripts(){
    wp_enqueue_style( 'style', plugins_url( 'css/style.css' , __FILE__ ), array(), '1.0', 'all' );
}

add_action('init', 'dashboard_shortcodes_init');
add_action('wp_enqueue_scripts','register_dasboard_scripts');
