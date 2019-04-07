<?php

/**
 * The file that defines the output view data with different format (type1) class for the Spruce Beer Dashboard Plugin
 *
 *
 * @since 		1.0.0
 *
 * @package 	Dashboard
 * @subpackage 	Dashboard/view
 * @author 		Sergiy Koyev <skoev@hotmail.com>
 */
class Dashboard_View1 {

    /**
     * Build review header content based on the provided brewery
     */
    public static function build_header($brewery, &$out) {
        if( empty($brewery) ) {
            throw new Exception('The brewery data is empty.');
        }

        $out .= '<div class="dashboard-header-1">';    
        $out .= "<div><span class='inline-content'>Brewery Picture:</span><img width='80' src='{$brewery['brewery_label']}'/></div>";
        $out .= "<div>Brewery Name: <b>{$brewery['brewery_name']}</b> </div>";
        $out .= "<div>Brewery Country: <b>{$brewery['country_name']}</b> </div>";
        $out .= "<div>Brewery Beer Total: <b>{$brewery['beer_count']}</b> </div>";
        $out .= '</div>';                
    }

    /**
     * Build review content based on the provided items.
     */
    public static function build_content($items, &$out){
        if( empty($items) ) {
            throw new Exception('The items data is empty.');
        }

        foreach( $items as $key => $item ) {
            $brewery = $item['brewery'];
            $beer    = $item['beer'];

            $out .= '<div class="dashboard-header-1">';

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
    }
}