<?php

/**
 * Retrieves the post type of the event post
 *
 * @since 1.0.0
 * @return string post type name.
 */
if ( ! function_exists( 'fec_get_post_type' ) ) {
    function fec_get_post_type() {
        return FEC_POST_TYPE;
    }
}

/**
 * Get event list
 * 
 * @since 1.0.0
 * 
 * @param string $start_date    start date date format Y-m-d.
 * @param string $end_date    end date date format Y-m-d.
 * 
 * @return array
 */
if( ! function_exists( 'fec_get_events' ) ) {
    function fec_get_events( $start_date = null,  $end_date = null ) {

        $args = array(
            'post_type'      => 'fluent_events',
            'post_status'    => 'publish',
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'fec_event_date',
            'order'          => 'asc',
            'posts_per_page' => - 1,
            'category'       => '',
            'links'          => 'on'
        );

        // if have start and end date value, event will be show between two date.
        if( $start_date && $end_date ) {
            
            // Calculate the Unix timestamp for the first day of the month
            $start_timestamp = strtotime( $start_date );
            
            // Calculate the Unix timestamp for the last day of the month
            $end_timestamp = strtotime( $end_date );

            $args['meta_query'] = array(
                    array(
                        'key' => 'fec_event_date',
                        'value' => array($start_timestamp, $end_timestamp),
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC',
                    ),
                );
        }

        $events_array = array();

        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                global $post;
                $query->the_post();
                $startdate = get_post_meta( $post->ID, 'fec_event_date', true );
                $event = array( 
                    'title' => get_the_title(),
                    'link' => get_the_permalink(),
                );
                if( ! isset( $events_array[$startdate] ) ) {
                    $events_array[$startdate] = array( $event );
                } else {
                    array_push( $events_array[$startdate], $event );
                }
            }
        }
        wp_reset_postdata();

        return $events_array;
    }
}

/**
 * Calendar header
 * Display next and prev link with selectd month and year.
 * 
 * @since 1.0.0
 * 
 * @param int $month_number
 * @param int $year_number 
 * 
 * @return array
 */
if( ! function_exists( 'fec_calendar_header' ) ) {
    function fec_calendar_header( $month_number = null, $year_number = null ) {
        global $wp_locale;

        if( ! $month_number ) {
            $month_number = current_time( 'm' );
        }

        if( ! $year_number ) {
            $year_number  = current_time( 'Y' );
        }

        $prev_year  = $year_number;
        $next_year  = $year_number;
        $prev_month = $month_number - 1;
        $next_month = $month_number + 1;
        if ( $prev_month == 0 ) {
            $prev_month = 12;
            $prev_year  = $year_number - 1;
        }
        if ( $next_month == 13 ) {
            $next_month = 1;
            $next_year  = $year_number + 1;
        };

        $prev_link = sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-arrow-left-alt2"></span>%s</a>', admin_url( 'edit.php?post_type=fluent_events&page=fluent-events-calendar&month=' . $prev_month . '&year=' . $prev_year ),  __( 'Prev', 'fluent-events-calendar' ), __( 'Prev', 'fluent-events-calendar' ) );

        $next_link = sprintf( '<a href="%s" title="%s">%s<span class="dashicons dashicons-arrow-right-alt2"></span></a>', admin_url( 'edit.php?post_type=fluent_events&page=fluent-events-calendar&month=' . $next_month . '&year=' . $next_year ),  __( 'Next', 'fluent-events-calendar' ), __( 'Next', 'fluent-events-calendar' ) );

        $month_year = sprintf( '<h3>%1$s %2$s</h3>' , $wp_locale->get_month( $month_number ), $year_number );

        return $prev_link . "\n" . $month_year . "\n" . $next_link;
    }
}