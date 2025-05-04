<?php

    class PostStatsHelper
    {
        const TABLE = 'poststats_visits';

        const COUNTRIES_TABLE_LENGTH = 5;
        const CITIES_TABLE_LENGTH = 5;

		function __construct(){
		    }
 
		
				/**
		 * Recursive sanitation for text or array
		 * 
		 * @param $array_or_string (array|string)
		 * @since  0.1
		 * @return mixed
		 */
		public static function sanitize_text_or_array_field($array_or_string)
		{
			if (is_string($array_or_string)) {
				$array_or_string = sanitize_text_field($array_or_string);
			} elseif (is_array($array_or_string)) {
				foreach ($array_or_string as $key => &$value) {
					if (is_array($value)) {
						$value = sanitize_text_or_array_field($value);
					} else {
						$value = sanitize_text_field($value);
					}
				}
			}

			return $array_or_string;
		}


        public static function assets ()
        {
			
            wp_register_style ('poststats_css_jqplot',      plugin_dir_url (__FILE__) . 'css/jquery.jqplot.min.css', [], '1.0.8');
            wp_register_style ('poststats_css_jquery_ui',   plugin_dir_url (__FILE__) . 'css/jquery-ui.css', [], '1.11.4');
            wp_register_style ('poststats_css_admin',       plugin_dir_url (__FILE__) . 'css/style.css', false, '1.0');

            wp_register_script ('poststats_js_jqplot',                        plugin_dir_url (__FILE__) . 'js/jquery.jqplot.min.js', array( 'jquery', 'jquery-ui-datepicker' ) , '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_cursor',                 plugin_dir_url (__FILE__) . 'js/jqplot.cursor.min.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_pointLabels',            plugin_dir_url (__FILE__) . 'js/jqplot.pointLabels.min.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_barRenderer',            plugin_dir_url (__FILE__) . 'js/jqplot.barRenderer.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_canvasAxisTickRenderer', plugin_dir_url (__FILE__) . 'js/jqplot.canvasAxisTickRenderer.js', false, '1.0.8', true);
            
			
			wp_register_script ('poststats_js_jqplot_canvasTextRenderer',     plugin_dir_url (__FILE__) . 'js/jqplot.canvasTextRenderer.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_pieRenderer',            plugin_dir_url (__FILE__) . 'js/jqplot.pieRenderer.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_dateAxisRenderer',       plugin_dir_url (__FILE__) . 'js/jqplot.dateAxisRenderer.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_jqplot_categoryAxisRenderer',   plugin_dir_url (__FILE__) . 'js/jqplot.categoryAxisRenderer.js', false, '1.0.8', true);
            wp_register_script ('poststats_js_admin',                         plugin_dir_url (__FILE__) . 'js/main.js', false, '2.2', true);

            wp_enqueue_style ('poststats_css_jqplot');
            wp_enqueue_style ('poststats_css_admin');
            wp_enqueue_style ('poststats_css_jquery_ui');

            ?>
            <script type="application/javascript">
                var POSTSTATS_PLUGIN_URL = "<?php echo plugin_dir_url (__FILE__); ?>";
                var POSTSTATS_ADMIN_AJAX = "<?php echo admin_url ('admin-ajax.php'); ?>";
            </script>
            <?php

            wp_enqueue_script ('poststats_js_jqplot');
            wp_enqueue_script ('poststats_js_jqplot_cursor');
            wp_enqueue_script ('poststats_js_jqplot_pointLabels');
            wp_enqueue_script ('poststats_js_jqplot_barRenderer');
            wp_enqueue_script ('poststats_js_jqplot_pieRenderer');
            wp_enqueue_script ('poststats_js_jqplot_canvasTextRenderer');
            wp_enqueue_script ('poststats_js_jqplot_canvasAxisTickRenderer');
            wp_enqueue_script ('poststats_js_jqplot_dateAxisRenderer');
            wp_enqueue_script ('poststats_js_jqplot_categoryAxisRenderer');
            wp_enqueue_script ('poststats_js_admin');
        }

        public static function install ()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . self::TABLE;
            if ($wpdb->get_var ("show tables like '$table_name'") != $table_name) {
                require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

                $sql = "CREATE TABLE `$table_name` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `post_id` int(11) NOT NULL,
                    `ip` varchar(255) default NULL,
                    `country` varchar(255) default NULL,
                    `country_code` varchar(255) default NULL,
                    `city` varchar(255) default NULL,
                    `created_at` varchar(20) default NULL,
                    PRIMARY KEY  (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";

                dbDelta ($sql);
            }
        }

        public static function uninstall ()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . self::TABLE;

            $wpdb->query ($wpdb->prepare ("DROP TABLE IF EXISTS %s", $table_name));
			
			$sps_posttypes = get_post_types( [], 'objects' );
			foreach ( $sps_posttypes as $type ) {
				if ( isset( $type->rewrite->slug ) ) {
					// you'll probably want to do something else.
				  
					if(!empty($type->rewrite->slug))
					{
						
						remove_filter( "manage_".$type->rewrite->slug."_posts_columns", 'filter_manage_'.$type->rewrite->slug.'_posts_columns', 10, 3 ); 
					
					}
			
				}
			}
			
			
        }

        public static function isBot ($user_agent)
        {
            $bots = [
					0  => 'bot',
					1  => 'spider',
					2  => 'crawl',
					3  => 'google',
					4  => 'msn',
					5  => 'aol',
					6  => 'yahoo',
					7  => 'bingbot',         // Bing
					8  => 'baiduspider',     // Baidu
					9  => 'yandexbot',       // Yandex
					10 => 'facebookexternalhit', // Facebook
					11 => 'twitterbot',      // Twitter
					12 => 'duckduckbot',     // DuckDuckGo
					13 => 'ahrefsbot',       // Ahrefs SEO
					14 => 'semrushbot',      // Semrush SEO
					15 => 'mj12bot',         // Majestic
					16 => 'sogou',           // Sogou (China)
					17 => 'slurp',           // Yahoo’s older crawler
					18 => 'archive.org_bot', // Internet Archive
					19 => 'ia_archiver',     // Internet Archive
					20 => 'rogerbot',        // Moz
				];


            $user_agent = strtolower ($user_agent);

            foreach ($bots as $bot) {
                if (strpos ($user_agent, $bot) !== false) {
                    return true;
                }
            }

            return false;
        }

        public static function head ()
        {

        }

        public static function admin_head ()
        {
            add_action ('admin_enqueue_scripts',  array( __CLASS__, 'assets' ) );
        }

        public static function get_visitor_ip ()
        {
            try {
                // check for shared internet/ISP IP
                if (!empty($_SERVER[ 'HTTP_CLIENT_IP' ]) && self::validate_ip ($_SERVER[ 'HTTP_CLIENT_IP' ])) {
                    return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_CLIENT_IP' ]);
                }

                // check for IPs passing through proxies
                if (!empty($_SERVER[ 'HTTP_X_FORWARDED_FOR' ])) {
                    // check if multiple ips exist in var
                    if (strpos ($_SERVER[ 'HTTP_X_FORWARDED_FOR' ], ',') !== false) {
                        $iplist = explode (',', $_SERVER[ 'HTTP_X_FORWARDED_FOR' ]);
                        foreach ($iplist as $ip) {
                            if (self::validate_ip ($ip))
                                return self::sanitize_text_or_array_field($ip);
                        }
                    } else {
                        if (self::validate_ip ($_SERVER[ 'HTTP_X_FORWARDED_FOR' ]))
                            return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_X_FORWARDED_FOR' ]);
                    }
                }
                if (!empty($_SERVER[ 'HTTP_X_FORWARDED' ]) && self::validate_ip ($_SERVER[ 'HTTP_X_FORWARDED' ]))
                    return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_X_FORWARDED' ]);
                if (!empty($_SERVER[ 'HTTP_X_CLUSTER_CLIENT_IP' ]) && self::validate_ip ($_SERVER[ 'HTTP_X_CLUSTER_CLIENT_IP' ]))
                    return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_X_CLUSTER_CLIENT_IP' ]);
                if (!empty($_SERVER[ 'HTTP_FORWARDED_FOR' ]) && self::validate_ip ($_SERVER[ 'HTTP_FORWARDED_FOR' ]))
                    return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_FORWARDED_FOR' ]);
                if (!empty($_SERVER[ 'HTTP_FORWARDED' ]) && self::validate_ip ($_SERVER[ 'HTTP_FORWARDED' ]))
                    return self::sanitize_text_or_array_field($_SERVER[ 'HTTP_FORWARDED' ]);

                // return unreliable ip since all else failed
                return self::sanitize_text_or_array_field($_SERVER[ 'REMOTE_ADDR' ]);
            } catch (Exception $e) {
            }

            return '127.0.0.1';
        }

        public static function validate_ip ($ip)
        {
            if (strtolower ($ip) === 'unknown')
			{
                return false;
			}
            // generate ipv4 network address
            $ip = ip2long ($ip);

            // if the ip is set and not equivalent to 255.255.255.255
            if ($ip !== false && $ip !== -1) {
                // make sure to get unsigned long representation of ip
                // due to discrepancies between 32 and 64 bit OSes and
                // signed numbers (ints default to signed in PHP)
                $ip = sprintf ('%u', $ip);
                // do private network range checking
				// IPv6
				
                if ($ip >= 0 && $ip <= 50331647) return false;
                if ($ip >= 167772160 && $ip <= 184549375) return false;
                if ($ip >= 2130706432 && $ip <= 2147483647) return false;
                if ($ip >= 2851995648 && $ip <= 2852061183) return false;
				
                if ($ip >= 2886729728 && $ip <= 2887778303) return false;
                if ($ip >= 3221225984 && $ip <= 3221226239) return false;
                if ($ip >= 3232235520 && $ip <= 3232301055) return false;
                if ($ip >= 4294967040) return false;
            }

            return true;
        }

        public static function track ($wpdb, $post)
        {
            $vtr_ip_address = PostStatsHelper::get_visitor_ip ();
			
			
			
			  $geoplugin_countryCode = '';
			  $ahc_region = '';

			 
			  $ahc_data = wp_remote_get("http://ip-api.com/json/".$vtr_ip_address);
			  $ip_data = json_decode(wp_remote_retrieve_body($ahc_data));

			  $country =  isset($ip_data->country) ? $ip_data->country : '';
			  $countryCode =  isset($ip_data->countryCode) ? strtolower($ip_data->countryCode) : '';
			  $city =  isset($ip_data->city) ? $ip_data->city : '';
				
			
            try {
                $wpdb->insert (
                    $wpdb->prefix . PostStatsHelper::TABLE,
                    [
                        'post_id'      => $post->ID,
                        'ip'           => $ip,
                        'country'      => $country,
                        'country_code' => $countryCode,
                        'city'         => $city,
                        'created_at'   => date ('Y-m-d H:i:s'),
                    ]
                );
            } catch (Exception $e) {
            }
        }

        public static function register_column ($defaults)
        {
            $defaults[ 'poststats_views_count' ] = 'Views Count';

            return $defaults;
        }

        public static function getSimpleVisits ($wpdb, $post_id, $from = null, $to = null)
        {
            try {
                $sql = "SELECT count(*) as counts " .
                       "FROM `" . $wpdb->prefix . self::TABLE . "` " .
                       "WHERE `post_id`='" . $post_id . "'";

                if ($from && $to) {
                    $sql .= " AND (`created_at` BETWEEN '" . $from . "' AND '" . $to . "')";
                }

                return $wpdb->get_var ($sql);
            } catch (Exception $e) {
            }

            return 0;
        }

        public static function show_visits_column ($post_ID)
        {
            global $wpdb;

            try {
                $TODAY_START = date ('Y-m-d 00:00:00');
                $TODAY_END = date ('Y-m-d 23:59:59');
                $YESTERDAY_START = date ('Y-m-d 00:00:00', time () - 60 * 60 * 24);
                $YESTERDAY_END = date ('Y-m-d 23:59:59', time () - 60 * 60 * 24);
                $WEEK_AGO = date ('Y-m-d 00:00:00', time () - 60 * 60 * 24 * 7);
                $MONTH_AGO = date ('Y-m-d 00:00:00', time () - 60 * 60 * 24 * 31);
                $YEAR_AGO = date ('Y-m-d 00:00:00', time () - 60 * 60 * 24 * 365);

                $CHART_VISITS_DEFAULT_START = date ('Y-m-d', time () - 60 * 60 * 24 * 14);
                $CHART_VISITS_DEFAULT_END = date ('Y-m-d');

                $POST_TITLE = $wpdb->get_var ("SELECT post_title FROM $wpdb->posts WHERE ID='" . $post_ID . "'");

                $visits_today = self::getSimpleVisits ($wpdb, $post_ID, $TODAY_START, $TODAY_END);
                $visits_yesterday = self::getSimpleVisits ($wpdb, $post_ID, $YESTERDAY_START, $YESTERDAY_END);
                $visits_week = self::getSimpleVisits ($wpdb, $post_ID, $WEEK_AGO, $TODAY_END);
                $visits_month = self::getSimpleVisits ($wpdb, $post_ID, $MONTH_AGO, $TODAY_END);
                $visits_year = self::getSimpleVisits ($wpdb, $post_ID, $YEAR_AGO, $TODAY_END);
                $visits_total = self::getSimpleVisits ($wpdb, $post_ID);
                ?>

                <a href="#id_views<?php echo intval($post_ID); ?>" class="cn_popup-views" name="<?php echo intval($post_ID); ?>">
                    <?php echo intval($visits_total); ?>
                    <span class="dashicons dashicons-chart-bar"></span>
                </a>

                <div id="id_views<?php echo intval($post_ID); ?>" class="poststats_modal_block"
                     data-post-id="<?php echo intval($post_ID); ?>"
                     data-page="1"
                     style="display: none;">
                    <div class="popup_block_center">
                        <ul class="tabs-menu">
                            <li class="current"><a href="#tab-1">Posts statistics</a></li>
                            <li><a href="#tab-2">GeoLocation box</a></li>
                        </ul>

                        <div class="exit-btn">×</div>

                        <div class="poststats_tab">
                            <div id="tab-1" class="poststats_tab-content">
                                <h2 class="title_vs">
								<b><?php echo self::sanitize_text_or_array_field($POST_TITLE); ?></b></h2>
								<br/>
								<center>
								<a target="_blank" title="download now" href="https://www.plugins-market.com/product/visitor-statistics-pro/"><img height="60px" border="0" src="<?php echo plugin_dir_url (__FILE__);?>images/upgrade-button-orange.png"></a>
								<br/>
								<b>Upgrade to Visitor Statistics Pro to get more awesome features :)<br/>
								<br/>
								</center>
                                <table class="table_stat">
                                    <tr>
                                        <td class="td-style-cell">Today</td>
                                        <td class="td-counts"><?php echo intval($visits_today); ?></td>
                                        <td class="td-style-cell">This Month</td>
                                        <td class="td-counts"><?php echo intval($visits_month); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="td-style-cell">Yesterday</td>
                                        <td class="td-counts"><?php echo intval($visits_yesterday); ?></td>
                                        <td class="td-style-cell">This Year</td>
                                        <td class="td-counts"><?php echo intval($visits_year); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="td-style-cell">This Week</td>
                                        <td class="td-counts"><?php echo intval($visits_week); ?></td>
                                        <td class="td-style-cell">Total</td>
                                        <td class="td-counts"><?php echo intval($visits_total); ?></td>
                                    </tr>
                                </table>

                                <br>
                                <br>

                                <div style="text-align:center;">
                                    <label for="from">From</label>&nbsp;
                                    <input type="text"
                                           id="poststats_tab_1_from_<?php echo intval($post_ID); ?>"
                                           name="fr_graph<?php echo intval($post_ID); ?>"
                                           value="<?php echo self::sanitize_text_or_array_field($CHART_VISITS_DEFAULT_START); ?>"/>

                                    &nbsp;&nbsp;&nbsp;
                                    <div class="lineclearfix" style="display: none;"></div>

                                    <label for="to"> To </label>&nbsp;
                                    <input type="text"
                                           id="poststats_tab_1_to_<?php echo intval($post_ID); ?>"
                                           name="tg_graph<?php echo intval($post_ID); ?>"
                                           value="<?php echo self::sanitize_text_or_array_field($CHART_VISITS_DEFAULT_END); ?>"/>
                                </div>

                                <br>

                                <div class="wrapchart_1_<?php echo intval($post_ID); ?>">Loading...</div>

                                <br><br>
                            </div>

                            <div id="tab-2" class="poststats_tab-content">
                                <h2 class="title_vs"><b><?php echo self::sanitize_text_or_array_field($POST_TITLE); ?></b></h2>
								<br/>
								<center>
								<a target="_blank" title="download now" href="https://www.plugins-market.com/product/visitor-statistics-pro/"><img height="60px" border="0" src="<?php echo plugin_dir_url (__FILE__);?>images/upgrade-button-orange.png"></a>
								<br/>
								<b>Upgrade to Visitor Statistics Pro to get more awesome features :)<br/>
								<br/>
								</center>
                                <div style="text-align:center;">
                                    <label for="from">From</label>&nbsp;
                                    <input type="text"
                                           id="poststats_tab_2_from_<?php echo intval($post_ID); ?>"
                                           name="from<?php echo intval($post_ID); ?>"
                                           value="<?php echo self::sanitize_text_or_array_field($CHART_VISITS_DEFAULT_START); ?>"/>

                                    &nbsp;&nbsp;&nbsp;
                                    <div class="lineclearfix" style="display: none;"></div>

                                    <label for="to">To </label>&nbsp;
                                    <input type="text"
                                           id="poststats_tab_2_to_<?php echo intval($post_ID); ?>"
                                           name="to<?php echo intval($post_ID); ?>"
                                           value="<?php echo self::sanitize_text_or_array_field($CHART_VISITS_DEFAULT_END); ?>"/>
                                </div>

                                <div class="wrap-table-locations">
                                    <div class="countries-pagination"></div>

                                    <table class="table_stat geolocations table-countries-list"
                                           style="width:90%; margin: 0 auto;margin-top:25px;">
                                        <thead>
                                        <tr>
                                            <td class="td-style-cell">Flag</td>
                                            <td class="td-style-cell">Country</td>
                                            <td class="td-style-cell">Hits</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="3">Loading...</td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <table class="table_stat geolocations table-cities-list"
                                           style="width:90%; margin: 0 auto;margin-top:25px; display:none;">
                                        <caption></caption>
                                        <thead>
                                        <tr>
                                            <td class="td-style-cell">Flag</td>
                                            <td class="td-style-cell">City</td>
                                            <td class="td-style-cell">Data/Time</td>
                                            <td class="td-style-cell">IP Address</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="4">Loading...</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <br>

                                <div class="wrapchart_2_<?php echo intval($post_ID); ?>">Loading...</div>

                                <br><br>

                                <div class="poststats_clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } catch (Exception $e) {
            }
        }

        public static function getDataForFirstChart ($data)
        {
            global $wpdb;

            $response_visits = [];
            $response_visitors = [];

            $table_name = $wpdb->prefix . self::TABLE;

            $from = date ('Y-m-d 00:00:00', strtotime ($data[ "from" ]));
            $to = date ('Y-m-d 23:59:59', strtotime ($data[ "to" ]));

            try {
                $visits = $wpdb->get_results (
                    "SELECT count(*) as visits_count, DATE(created_at) as visits_date " .
                    "FROM $table_name " .
                    "WHERE created_at BETWEEN '" . $from . "' AND '" . $to . "' AND post_id='" . $data[ "post_id" ] . "' " .
                    "GROUP BY visits_date"
                );

                if (sizeof ($visits)) {
                    foreach ($visits as $item) {
                        array_push ($response_visits, [
                            $item->visits_date,
                            (int)$item->visits_count,
                        ]);
                    }
                }
            } catch (Exception $e) {
            }

            try {
                $visitors = $wpdb->get_results (
                    "SELECT count(distinct ip) as visitors_count, DATE(created_at) as visits_date " .
                    "FROM $table_name " .
                    "WHERE created_at BETWEEN '" . $from . "' AND '" . $to . "' AND post_id='" . $data[ "post_id" ] . "' " .
                    "GROUP BY visits_date"
                );

                if (sizeof ($visitors)) {
                    foreach ($visitors as $item) {
                        array_push ($response_visitors, [
                            $item->visits_date,
                            (int)$item->visitors_count,
                        ]);
                    }
                }
            } catch (Exception $e) {
            }

            return [
                'visits'   => $response_visits,
                'visitors' => $response_visitors,
            ];
        }

        public static function getDataCountriesTable ($data)
        {
            global $wpdb;

            $response_table = [];
            $response_chart_both = [];
            $response_chart = [];
            $response_chart_titles = [];

            $table_name = $wpdb->prefix . self::TABLE;

            $from = date ('Y-m-d 00:00:00', strtotime ($data[ "from" ]));
            $to = date ('Y-m-d 23:59:59', strtotime ($data[ "to" ]));

            $page = (int)$data[ 'page' ] ? : 1;
            $limit = self::COUNTRIES_TABLE_LENGTH;
            $offset = $limit * $page - $limit;

            try {
                $response_table = $wpdb->get_results (
                    "SELECT count(id) as visits_count, country, country_code " .
                    "FROM $table_name " .
                    "WHERE created_at BETWEEN '" . $from . "' AND '" . $to . "' AND post_id='" . $data[ "post_id" ] . "' " .
                    "GROUP BY country  " .
                    "ORDER BY visits_count desc "
                );
				
            } catch (Exception $e) {
            }

            try {
                $rows = $wpdb->get_results (
                    "SELECT count(*) as visits_count, country " .
                    "FROM $table_name " .
                     "WHERE created_at BETWEEN '" . $from . "' AND '" . $to . "' AND post_id='" . $data[ "post_id" ] . "' " .
                    "GROUP BY country " .
                    "ORDER BY visits_count DESC " .
                    "LIMIT 0,10"
                );

                if (sizeof ($rows)) {
                    foreach ($rows as $item) {
                        $response_chart[] = (int)$item->visits_count;
                        $response_chart_titles[] = $item->country;

                        array_push ($response_chart_both, [
                            $item->country,
                            (int)$item->visits_count,
                        ]);
                    }
                }
            } catch (Exception $e) {
            }

            return [
                'table'        => array_slice ($response_table, $offset, $limit),
                'chart'        => $response_chart,
                'chart_titles' => $response_chart_titles,
                'chart_both'   => $response_chart_both,
                'page'         => $page,
                'total_pages'  => (int)(sizeof ($response_table) / $limit) + 1,
            ];
        }

        public static function getDataCitiesTable ($data)
        {
            global $wpdb;

            $response_cities = [];

            $table_name = $wpdb->prefix . self::TABLE;

            $from = date ('Y-m-d 00:00:00', strtotime ($data[ "from" ]));
            $to = date ('Y-m-d 23:59:59', strtotime ($data[ "to" ]));

            $page = (int)$data[ 'page' ] ? : 1;

            try {
                $response_cities = $wpdb->get_results (
                    "SELECT * " .
                    "FROM $table_name " .
                    "WHERE created_at BETWEEN '" . $from . "' AND '" . $to . "' AND post_id='" . $data[ "post_id" ] . "' AND country_code='" . $data[ 'country_code' ] . "' " .
                    "ORDER BY created_at DESC"
                );

                if (sizeof ($response_cities)) {
                    foreach ($response_cities as &$item) {
                        $item->create_at_formatted = date ('d M Y @ h:i a', strtotime ($item->created_at));
                    }
                }
            } catch (Exception $e) {
            }

            $offset = ( self::CITIES_TABLE_LENGTH * $page ) - self::CITIES_TABLE_LENGTH;

            return [
                'cities'      => array_slice ($response_cities, $offset, self::CITIES_TABLE_LENGTH),
                'total_rows'  => sizeof ($response_cities),
                'total_pages' => (int)(sizeof ($response_cities) / self::CITIES_TABLE_LENGTH) + 1,
                'next_page'   => $page + 1,
            ];
        }
    }