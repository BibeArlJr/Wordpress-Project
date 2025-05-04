(function($) {
    var PLOTS         = {};
    var PLOTS_CONFIGS = {};

    $(".tabs-menu a").click(function(event) {
        event.preventDefault();

        var target_tab = $(this).attr("href");

        $(this).closest('li').addClass("current");
        $(this).closest('li').siblings().removeClass("current");

        $(this).closest('.popup_block_center').find(".poststats_tab-content").hide();
        $(this).closest('.popup_block_center').find(target_tab).fadeIn();

        var $modal = $(this).closest('.poststats_modal_block');

        if (target_tab == '#tab-1') {
            draw_first_chart($modal);
        } else if (target_tab == '#tab-2') {
            draw_countries_table($modal, 1);
        }
    });

    $("input[id^='poststats_tab_1_from_']").datepicker({
        dateFormat    : "yy-mm-dd",
        changeMonth   : true,
        changeYear    : true,
        numberOfMonths: 1,
        onSelect      : function(selectedDate) {
            var $modal = $(this).closest('.poststats_modal_block');

            $modal.find("input[id^='poststats_tab_1_to_']").datepicker("option", "minDate", selectedDate);

            draw_first_chart($modal);
        }
    });

    $("input[id^='poststats_tab_1_to_']").datepicker({
        dateFormat    : "yy-mm-dd",
        changeMonth   : true,
        changeYear    : true,
        numberOfMonths: 1,
        onSelect      : function(selectedDate) {
            var $modal = $(this).closest('.poststats_modal_block');

            $modal.find("input[id^='poststats_tab_1_from_']").datepicker("option", "maxDate", selectedDate);

            draw_first_chart($modal);
        }
    });

    $("input[id^='poststats_tab_2_from_']").datepicker({
        dateFormat    : "yy-mm-dd",
        changeMonth   : true,
        changeYear    : true,
        numberOfMonths: 1,
        onSelect      : function(selectedDate) {
            var $modal = $(this).closest('.poststats_modal_block');

            $modal.find("input[id^='poststats_tab_2_to_']").datepicker("option", "minDate", selectedDate);

            draw_countries_table($modal, 1);
        }
    });

    $("input[id^='poststats_tab_2_to_']").datepicker({
        dateFormat    : "yy-mm-dd",
        changeMonth   : true,
        changeYear    : true,
        numberOfMonths: 1,
        onSelect      : function(selectedDate) {
            var $modal = $(this).closest('.poststats_modal_block');

            $modal.find("input[id^='poststats_tab_2_from_']").datepicker("option", "maxDate", selectedDate);

            draw_countries_table($modal, 1);
        }
    });

    // SHOW MODAL
    $('.cn_popup-views').click(function(e) {
        e.preventDefault();

        var target_modal = $(this).attr('href');
        var $modal       = $(target_modal)

        $modal.fadeIn();
        $('body').addClass('poststats_noscroll');

        draw_first_chart($modal);
        draw_countries_table($modal, 1);
    });

    // CLOSE MODAL
    $('.exit-btn').click(function(e) {
        e.preventDefault();

        $(this.closest('.poststats_modal_block')).fadeOut();
        $('body').removeClass('poststats_noscroll');
    });

    $('body').on('click', '.table_stat caption .back', function(e) {
        e.preventDefault();

        var $modal = $(this).closest('.poststats_modal_block');

        $modal.find('.table-countries-list').show();
        $modal.find('.countries-pagination').show();
        $modal.find('.table-cities-list').hide();
    });

    $('body').on('click', '.poststats_pagination.cities .poststats_pagination_item', function(e) {
        e.preventDefault();

        $(this).closest('ul.poststats_pagination').find('li').removeClass('poststats_pagination_item_active');
        $(this).addClass('poststats_pagination_item_active');

        var page         = $(this).data('page');
        var post_id      = $(this).data('post-id');
        var country_code = $(this).data('country-code');
        var $modal       = $(this).closest('.poststats_modal_block');

        getCities($modal, post_id, country_code, page, false);
    });

    $('body').on('click', '.poststats_pagination.countries .poststats_pagination_item', function(e) {
        e.preventDefault();

        $(this).closest('ul.poststats_pagination').find('li').removeClass('poststats_pagination_item_active');
        $(this).addClass('poststats_pagination_item_active');

        var $modal = $(this).closest('.poststats_modal_block');

        draw_countries_table($modal, $(this).data('page'));
    });

    $('body').on('click', '.country_open', function(e) {
        e.preventDefault();

        var post_id      = $(this).data('post-id');
        var country_code = $(this).data('country-code');
        var $modal       = $(this).closest('.poststats_modal_block');

        $modal.find('.table-countries-list').hide();
        $modal.find('.countries-pagination').hide();
        $modal.find('.table-cities-list').show();

        $modal.data('page', 1);

        var $html = '<a class="back"><span class="dashicons dashicons-undo"></span> back</a> ' +
                    $(this).text() +
                    '<ul class="poststats_pagination cities">' +
                    '<li class="poststats_pagination_item poststats_pagination_item_active">1</li>' +
                    '</ul>';

        $modal.find('.table-cities-list caption').html($html);

        getCities($modal, post_id, country_code, 1, true);
    });

    function getCities($modal, post_id, country_code, page, draw_pagination) {
        $.ajax({
            url       : POSTSTATS_ADMIN_AJAX,
            type      : "post",
            dataType  : 'json',
            data      : {
                action      : 'poststats_cities_table',
                from        : $modal.find("input[id^='poststats_tab_2_from_']").val(),
                to          : $modal.find("input[id^='poststats_tab_2_to_']").val(),
                page        : page,
                post_id     : post_id,
                country_code: country_code
            },
            beforeSend: function() {
                if (draw_pagination) {
                    $modal.find('.table-cities-list tbody').html('<tr><td colspan="4">Loading...</td></tr>');
                }
            },
            success   : function(response) {
                $modal.find('.table-cities-list tbody').html("");

                if (draw_pagination) {
                    var $options = '<li data-country-code="' + country_code + '" data-post-id="' + post_id + '" class="poststats_pagination_item poststats_pagination_item_active">1</li>';

                    for (var i = 2; i <= response.total_pages; i++) {
                        $options += '<li data-page="' + i + '" data-country-code="' + country_code + '" data-post-id="' + post_id + '" class="poststats_pagination_item">' + i + '</li>';
                    }

                    $modal.find('.poststats_pagination.cities').html($options);
                }

                if (response.cities.length) {
                    for (var i in response.cities) {
                        if (!response.cities.hasOwnProperty(i)) continue;

                        $modal.find('.table-cities-list tbody').append(
                            "<tr>" +
                            "<td><img src='" + POSTSTATS_PLUGIN_URL + 'images/flag/' + response.cities[ i ].country_code + ".png' /></td>" +
                            "<td>" + response.cities[ i ].city + "</td>" +
                            "<td>" + response.cities[ i ].create_at_formatted + "</td>" +
                            "<td>" + response.cities[ i ].ip + "</td>" +
                            "</tr>"
                        );
                    }
                }
            }
        });
    }

    // DRAW FIRST CHART
    function draw_first_chart($modal) {
        var post_id = $modal.data('post-id');

        $.ajax({
            url       : POSTSTATS_ADMIN_AJAX,
            type      : "post",
            dataType  : 'json',
            data      : {
                action : 'poststats_first_chart',
                from   : $modal.find("input[id^='poststats_tab_1_from_']").val(),
                to     : $modal.find("input[id^='poststats_tab_1_to_']").val(),
                post_id: post_id
            },
            beforeSend: function() {
                jQuery(".wrapchart_1_" + post_id).html('Loading...');
            },
            success   : function(response) {
                var $newDiv = jQuery("<div></div>");

                $newDiv.attr("id", "graph_tab1_" + post_id);

                jQuery(".wrapchart_1_" + post_id).html('');
                jQuery(".wrapchart_1_" + post_id).append($newDiv);

                PLOTS_CONFIGS[ "plot" + post_id ] = {
                    title : 'Daily Hits',
                    axes  : {
                        xaxis: {
                            renderer: jQuery.jqplot.DateAxisRenderer
                        }
                    },
                    legend: {
                        show           : true,
                        placement      : 'outsideGrid',
                        rendererOptions: {
                            numberRows: 1
                        },
                        location       : 's',
                        marginTop      : '15px'
                    },
                    series: [
                        {
                            label        : 'Visit',
                            lineWidth    : 2,
                            markerOptions: {
                                style: "circle"
                            }
                        },
                        {
                            label        : 'Visitor',
                            lineWidth    : 2,
                            markerOptions: {
                                style: "circle"
                            }
                        }
                    ]

                };

                PLOTS[ "plot" + post_id ] = jQuery.jqplot("graph_tab1_" + post_id, [
                    response.visits, response.visitors
                ], PLOTS_CONFIGS[ "plot" + post_id ]);
            }
        });
    }

    function draw_countries_table($modal, page) {
        var post_id = $modal.data('post-id');

        $.ajax({
            url       : POSTSTATS_ADMIN_AJAX,
            type      : "post",
            dataType  : 'json',
            data      : {
                action : 'poststats_countries_table',
                from   : $modal.find("input[id^='poststats_tab_2_from_']").val(),
                to     : $modal.find("input[id^='poststats_tab_2_to_']").val(),
                post_id: post_id,
                page   : page
            },
            beforeSend: function() {
                $modal.find('.table-countries-list tbody').html('<tr><td colspan="3">Loading...</td></tr>');
                jQuery(".wrapchart_2_" + post_id).html('Loading...');
            },
            success   : function(response) {
                var $html = '<ul class="poststats_pagination countries">';

                for (var i = 1; i <= response.total_pages; i++) {
                    if (page == i) {
                        $html += '<li data-page="' + i + '" data-post-id="' + post_id + '" class="poststats_pagination_item poststats_pagination_item_active">' + i + '</li>';
                    } else {
                        $html += '<li data-page="' + i + '" data-post-id="' + post_id + '" class="poststats_pagination_item">' + i + '</li>';
                    }
                }

                $html += '</ul>';

                $modal.find('.countries-pagination').html($html);

                $modal.find('.table-countries-list tbody').html("");

                if (response.table.length) {
                    for (var i in response.table) {
                        if (!response.table.hasOwnProperty(i)) continue;

                        $modal.find('.table-countries-list tbody').append(
                            "<tr>" +
                            "<td><img src='" + POSTSTATS_PLUGIN_URL + 'images/flag/' + response.table[ i ].country_code + ".png' /></td>" +
                            "<td><a data-country-code='" + response.table[ i ].country_code + "' data-post-id='" + post_id + "' class='country_open'>" + response.table[ i ].country + "<a></td>" +
                            "<td>" + response.table[ i ].visits_count + "</td>" +
                            "</tr>"
                        );
                    }
                }

                var $newDiv = jQuery("<div></div>");

                $newDiv.attr("id", "graph_tab2_" + post_id);

                jQuery(".wrapchart_2_" + post_id).html('');
                jQuery(".wrapchart_2_" + post_id).append($newDiv);

                setTimeout(function() {
                    PLOTS_CONFIGS[ "plot_geo_" + post_id ] = {
                        title         : 'Hits Per Country',
                        axes          : {
                            xaxis: {
                                renderer    : $.jqplot.CategoryAxisRenderer,
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                                tickOptions : {
                                    angle   : 30,
                                    fontSize: '10pt'
                                }
                            },
                            yaxis: {
                                min         : 0,
                                tickInterval: 50,
                                tickOptions : {
                                    formatString: '%d'
                                }
                            }
                        },
						seriesColors:['#74b6e7'],
                        seriesDefaults: {
                            renderer: $.jqplot.BarRenderer,
                        },
                         series:[
						 {pointLabels:{
						   show: true,
						   location:'s',
						   ypadding : 5,
						 }, label: 'Country'}]
						 
                    };

                    PLOTS[ "plot_geo_" + post_id ] = jQuery.jqplot("graph_tab2_" + post_id, [ response.chart_both ], PLOTS_CONFIGS[ "plot_geo_" + post_id ]);
                }, 200);
            }
        });
    }
})(jQuery);

