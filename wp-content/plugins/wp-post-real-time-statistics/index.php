<?php
    /*
    Plugin Name: WP Post Statistics (Visitors & Visits Counter)
    Plugin URI: https://www.plugins-market.com/product/visitor-statistics-pro/
    Description: Hits counter that shows analytical numbers of your WordPress site visitors and hits
    Version: 2.9
    Author: osamaesh
    Author URI: https://www.plugins-market.com
    */

    if (!defined ('ABSPATH')) die('-1');

    require_once (__DIR__ . '/PostStatsHelper.php');

    register_activation_hook (__FILE__, array( 'PostStatsHelper', 'install' ) );
    register_uninstall_hook (__FILE__,  array( 'PostStatsHelper', 'uninstall' ) );

    add_filter ('manage_posts_columns', function ($defaults) {
        return PostStatsHelper::register_column ($defaults);
    });
	
	
	
	$sps_posttypes = get_post_types( [], 'objects' );
    foreach ( $sps_posttypes as $type ) {
        if ( isset( $type->rewrite->slug ) ) {
            // you'll probably want to do something else.
          
			if(!empty($type->rewrite->slug))
			{
				
				add_filter ('manage_'.$type->rewrite->slug.'_posts_columns', function ($defaults) {
					return PostStatsHelper::register_column ($defaults);
				});
				
				
				add_action ('manage_'.$type->rewrite->slug.'_posts_columns', function ($column_name, $post_ID) {
				if ($column_name != 'poststats_views_count') return;

				global $wpdb;

				PostStatsHelper::show_visits_column ($post_ID);

				return intval (PostStatsHelper::getSimpleVisits ($wpdb, $post_ID));
			}, 10, 2);
	
	
			}
	
        }
		
		
    }
	
	
    add_action ('manage_posts_custom_column', function ($column_name, $post_ID) {
        if ($column_name != 'poststats_views_count') return;

        global $wpdb;

        PostStatsHelper::show_visits_column ($post_ID);

        return intval (PostStatsHelper::getSimpleVisits ($wpdb, $post_ID));
    }, 10, 2);

    add_filter ('manage_edit-post_sortable_columns', function ($columns) {
        $columns[ 'poststats_views_count' ] = 'Views count';

        return $columns;
    });
	
    add_action ('admin_enqueue_scripts', function () {
        PostStatsHelper::assets();
    });

    add_action ('wp_head', function () {
        global $wpdb, $post;

        if (!wp_is_post_revision ($post) && !is_preview () && is_single ()) {
            if (!PostStatsHelper::isBot ($_SERVER[ 'HTTP_USER_AGENT' ])) {
                PostStatsHelper::track ($wpdb, $post);
            }
        }
    });

    add_action ('wp_ajax_poststats_first_chart', function () {
        echo json_encode (PostStatsHelper::getDataForFirstChart ($_POST));

        wp_die ();
    });

    add_action ('wp_ajax_poststats_countries_table', function () {
        echo json_encode (PostStatsHelper::getDataCountriesTable ($_POST));

        wp_die ();
    });

    add_action ('wp_ajax_poststats_cities_table', function () {
        echo json_encode (PostStatsHelper::getDataCitiesTable ($_POST));

        wp_die ();
    });
