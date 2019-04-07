=== Spruce Beer Dashboard Plugin ===

Contributors: Sergiy Koyev
Tags: spruce beer dashboard
Requires at least: 4.5
Tested up to: 4.9.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.3
Plugin URI: https://untappd.com/b/garrison-brewing-company-spruce-beer/110569

Dasboard webpage that lists the #item count most recent reviews for Spruce Beer.

== Description ==

A simple dasboard webpage that lists the #item count most recent reviews for Spruce Beer.

Major features in Spruce Beer Dashboard include:

* Integrated with API https://api.untappd.com/v4.
* Shows in the dashboard review header section the Brewery Info based on the API HTTP GET: https://untappd.com/api/docs#breweryinfo.
* Shows in the dashboard review content section the Recent Beer Reviews based on the API HTTP GET: https://untappd.com/api/docs#beeractivityfeed.

Shortcode example:

    [spruce_beer_dashboard server_url="https://api.untappd.com/v4/beer/checkins"
                           bid="110569"
                           brewery_id="1473" 
                           item_count="10" 
                           client_id="clientID123" 
			   view_type="type1"
                           client_secret="clientsecretID123"]Spruce Beer Dashboard[/spruce_beer_dashboard]


== Installation ==

1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **Spruce Beer Dashboard** and click "*Install now*"
2. Alternatively, download the plugin and upload the contents of `spruce-beer-dashboard.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
3. Activate the plugin
