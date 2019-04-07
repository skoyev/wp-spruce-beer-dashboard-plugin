<?php

/**
 * The file that defines the core plugin class for the Spruce Beer Dashboard Plugin
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin dashboard.
 *
 * @since 		1.0.0
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 		1.0.0
 * @package 	Dashboard
 * @subpackage 	Dashboard/includes
 * @author 		Sergiy Koyev <skoev@hotmail.com>
 */
class Dashboard {
    private static $initiated = false;

    public static function init() {
		if ( ! self::$initiated ) {
            self::init_dependencies();
			self::init_hooks();
		}
    }

    /**
     * Register dependencies.
     */
    private static function init_dependencies() {
        require_once DASHBOARD__PLUGIN_DIR . '/view/type1-dashboard-view.php';
        require_once DASHBOARD__PLUGIN_DIR . '/view/type2-dashboard-view.php';
    }

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
        self::$initiated = true;        
        
        add_action('wp_enqueue_scripts', array( 'Dashboard','register_dasboard_scripts'));        
        /**
         * Shortcode init function hook.
         */
        add_shortcode('spruce_beer_dashboard', array( 'Dashboard','spruce_beer_dashboard_creation'));
    }

    /**
     * Register custom style classes
     */
    public static function register_dasboard_scripts(){
        wp_enqueue_style( 'dashboard-public', plugins_url( '../assets/css/dashboard-public.css' , __FILE__ ), array(), '1.1', 'all' );
    }

    /**
     * Function which implements spruce_beer_dashboard short code. It displays Brewer Data in header and
     * most recent #item_count beer reviews.
     */
    public static function spruce_beer_dashboard_creation($atts, $content = null) {
        if(empty($atts)) {
            self::log_error('Error. Attributes is null.');
            return;
        }
    
        extract( shortcode_atts(array(
            'server_url' => 'server_url',  // base server URL
            'client_id'  => 'client_id',   // client request ID
            'client_secret' => 'client_secret', // client secret
            'brewery_id' => 'brewery_id',  // brewery_id
            'bid' => 'bid',                // beer id
            'item_count' => 'item_count',  // item count, optional
            'view_type'  => 'view_type'    // view type [type1, type2,...]
        ), $atts ) );
    
        if( empty($server_url) || 
                empty($client_id) || 
                    empty($client_secret)){
            log_error('Error. Server URL, Client ID or Client Secret is null.');
            return;
        }
    
        // secure output by executing the_content filter hook on $content
        $out = !empty($content) ? '<h3>'.apply_filters('the_content', $content).'</h3>' : '';
        try {
            // dashboard header
            self::dashboard_review_header($out, $server_url, $client_id, $client_secret, $brewery_id, $view_type);
            // dashboard content
            self::dashboard_review_content($out, $server_url, $client_id, $client_secret, $bid, $item_count, $view_type);
        } catch (Exception $e){
            self::log_error($e->getMessage());
        }
    
        return $out;
    }

    /**
     * Add Dashboard Header section. Fetch Brewery Info for the Header Section
     * From API: https://untappd.com/api/docs#breweryinfo
     */
    public static function dashboard_review_header(&$out, $server_url, $client_id, $client_secret, $brewery_id, $view_type){
        $fullServerURL = "{$server_url}/brewery/info";
        
        // BID url request param
        if( empty($brewery_id) ){
            self::log_error('Error. Brewery ID is null. No Dashboard header content is generated.');
            return;    
        }
    
        $fullServerURL .= "/{$brewery_id}";
    
        // client_id url request param
        $fullServerURL .= "?client_id={$client_id}&client_secret={$client_secret}";    
        
        $request = wp_remote_get($fullServerURL);
    
        // check if any wp http request errors                
        if( is_wp_error( $request ) ) {
            throw new Exception("WP request error...");
        }                
    
        $body     = wp_remote_retrieve_body( $request );
        $arrData  = json_decode($body, true);        
        $brewery  = $arrData['response']['brewery'];     

        // Output review header data based on the view type[type1, type2]
        switch ($view_type){
            case type1:
                Dashboard_View1::build_header($brewery, $out);
                break;
            case type2:
                Dashboard_View2::build_header($brewery, $out);
                break;
            default:                
                Dashboard_View1::build_header($brewery, $out);
                break;
        }    
    } 

    public static function dashboard_review_content(&$out, $server_url, $client_id, $client_secret, $bid, $item_count, $view_type) {
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
    
            //$body    = '{"meta":{"code":200,"response_time":{"time":0.035,"measure":"seconds"},"init_time":{"time":0,"measure":"seconds"}},"notifications":[],"response":{"pagination":{"since_url":"https:\/\/api.untappd.com\/v4\/beer\/checkins\/110569?max_id=729064846","next_url":"https:\/\/api.untappd.com\/v4\/beer\/checkins\/110569?max_id=728255692","max_id":728255692},"checkins":{"count":2,"items":[{"checkin_id":729064846,"created_at":"Thu, 28 Mar 2019 18:17:19 +0000","checkin_comment":"I absolutely loved this beer.  I will be getting more of this next time I go camping.  I feel like this next to campfire is perfection!","rating_score":5,"user":{"uid":814352,"user_name":"canadIAN","first_name":"Ian","last_name":"S.","location":"","url":"","is_supporter":0,"bio":"","relationship":null,"user_avatar":"https:\/\/gravatar.com\/avatar\/570e72e23a21f94bf09777143e024572?size=100&d=https%3A%2F%2Funtappd.akamaized.net%2Fsite%2Fassets%2Fimages%2Fdefault_avatar_v3_gravatar.jpg%3Fv%3D2","is_private":0},"beer":{"bid":110569,"beer_name":"Spruce Beer","beer_label":"https:\/\/untappd.akamaized.net\/site\/beer_logos\/beer-110569_76393_sm.jpeg","beer_abv":7.5,"beer_ibu":35,"beer_slug":"garrison-brewing-company-spruce-beer","beer_description":"North America’s oldest beer style brewed with local Spruce & Fir tips, blackstrap molasses and dates. Dark amber and brown colouring. Aroma is a comforting mix of spruce boughs, caramel malts, molasses and dates.","beer_style":"Spiced \/ Herbed Beer","has_had":false,"beer_active":1},"brewery":{"brewery_id":1473,"brewery_name":"Garrison Brewing Company","brewery_slug":"garrison-brewing-company","brewery_page_url":"\/GarrisonBrewingCompany","brewery_label":"https:\/\/untappd.akamaized.net\/site\/brewery_logos\/brewery-1473_1ed0b.jpeg","country_name":"Canada","contact":{"twitter":"GarrisonBrewing","facebook":"http:\/\/www.facebook.com\/pages\/Garrison-Brewing-Co\/184186131152","url":"http:\/\/www.garrisonbrewing.com"},"location":{"brewery_city":"Halifax","brewery_state":"NS","lat":44.6401,"lng":-63.5667},"brewery_active":1},"venue":[],"comments":{"total_count":0,"count":0,"items":[]},"toasts":{"total_count":1,"count":1,"auth_toast":null,"items":[{"uid":60485,"user":{"uid":60485,"user_name":"scubasteve416","first_name":"Steve","last_name":"M.","bio":"Tech geek; gamer; Leafs, Knights, Jays, Marlies, TFC and MMA fan; Ontario Craft Beer Pro; comic nerd; metalhead","location":"Toronto","relationship":"none","user_avatar":"https:\/\/untappd.akamaized.net\/profile\/aa47e7ef90c221f5b268f45782674195_100x100.jpg","account_type":"user","venue_details":[],"brewery_details":[]},"like_id":640133510,"like_owner":false,"created_at":"Thu, 28 Mar 2019 22:26:54 +0000"}]},"media":{"count":0,"items":[]},"source":{"app_name":"Untappd for iPhone - (V2)","app_website":"http:\/\/untpd.it\/iphoneapp"},"badges":{"retro_status":false,"count":0,"items":[]}},{"checkin_id":728255692,"created_at":"Mon, 25 Mar 2019 03:17:34 +0000","checkin_comment":"","rating_score":2.75,"user":{"uid":3929390,"user_name":"TheMightySumo","first_name":"Jamie","last_name":"T.","location":"","url":"","is_supporter":0,"bio":"","relationship":null,"user_avatar":"https:\/\/gravatar.com\/avatar\/38c9e37a5fa2a0bbc98a6d6bd11f0a34?size=100&d=https%3A%2F%2Funtappd.akamaized.net%2Fsite%2Fassets%2Fimages%2Fdefault_avatar_v3_gravatar.jpg%3Fv%3D2","is_private":0},"beer":{"bid":110569,"beer_name":"Spruce Beer","beer_label":"https:\/\/untappd.akamaized.net\/site\/beer_logos\/beer-110569_76393_sm.jpeg","beer_abv":7.5,"beer_ibu":35,"beer_slug":"garrison-brewing-company-spruce-beer","beer_description":"North America’s oldest beer style brewed with local Spruce & Fir tips, blackstrap molasses and dates. Dark amber and brown colouring. Aroma is a comforting mix of spruce boughs, caramel malts, molasses and dates.","beer_style":"Spiced \/ Herbed Beer","has_had":false,"beer_active":1},"brewery":{"brewery_id":1473,"brewery_name":"Garrison Brewing Company","brewery_slug":"garrison-brewing-company","brewery_page_url":"\/GarrisonBrewingCompany","brewery_label":"https:\/\/untappd.akamaized.net\/site\/brewery_logos\/brewery-1473_1ed0b.jpeg","country_name":"Canada","contact":{"twitter":"GarrisonBrewing","facebook":"http:\/\/www.facebook.com\/pages\/Garrison-Brewing-Co\/184186131152","url":"http:\/\/www.garrisonbrewing.com"},"location":{"brewery_city":"Halifax","brewery_state":"NS","lat":44.6401,"lng":-63.5667},"brewery_active":1},"venue":[],"comments":{"total_count":0,"count":0,"items":[]},"toasts":{"total_count":0,"count":0,"auth_toast":false,"items":[]},"media":{"count":0,"items":[]},"source":{"app_name":"Untappd for iPhone - (V2)","app_website":"http:\/\/untpd.it\/iphoneapp"},"badges":{"retro_status":false,"count":0,"items":[]}}]}}}';
            $body    = wp_remote_retrieve_body( $request );
            $arrData = json_decode($body, true);        

            $items   = $arrData['response']['checkins']['items'];        
            $out    .= "<span>Total Most Recent Reviews: <b>" .count($items). "</b></span>";

            // Output review content data based on the view type[type1, type2]
            switch ($view_type){
                case type1:
                    Dashboard_View1::build_content($items, $out);
                    break;
                case type2:
                    Dashboard_View2::build_content($items, $out);
                    break;
                default:                
                    Dashboard_View1::build_content($items, $out);
                    break;
            }        
        } else {
            throw new Exception('Error. Full Server URL is null.');
        }
    }    

    /**
     * Log error function for writing error log
     */
    public static function log_error($message) {
        if ( WP_DEBUG === true ) {
            if ( is_array($message) || is_object($message) ) {
                error_log( print_r($message, true) );
            } else {
                error_log( $message );
            }
        }
    }    
}