<?php

namespace Fluent_EC;

/**
 * CalendarPage class.
 *
 * Create Admin page and show calendar with event in this page.
 * 
 * @class CalendarPage
 */
class CalendarPage {

    /**
     * Display calendar with events
     * @since 1.0.0
     * 
     * @return void
     */
    public function calendar_output() {

        global $wp_locale;

        // add event calendar css
        wp_enqueue_style( 'fec-admin' );

        // week_begins = 0 stands for Sunday.
        $week_begins = (int) get_option( 'start_of_week' );

        // Get user secleted year, if not evaiable then get current year
        $year_number  = isset( $_REQUEST['year'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['year'] ) ) : current_time( 'Y' );
        
        // Get user secleted month, if not evaiable then get current month
        $month_number = isset( $_REQUEST['month'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['month'] ) ) : current_time( 'm' );

        // Create an integer Unix timestamp date. 
        $unixmonth = mktime( 0, 0, 0, $month_number, 1, $year_number );

        // Get last day of this month
        $last_day  = gmdate( 't', $unixmonth );

        // Create first day of the month
        $start_date = "$year_number-$month_number-01";
        
        // Create last day of the month
        $end_date = "$year_number-$month_number-$last_day";

        // Get list of events between start date and end daite
        $events_array = fec_get_events( $start_date, $end_date );

        $calendar_output = '<div id="fec" class="fluent-events-calendar">' . "\n";
        
        // Get Calendar next and prev link with current month and year
        $calendar_header =  fec_calendar_header( $month_number, $year_number );

        $calendar_output .= '<div class="calendar-header"> ' . "\n" . $calendar_header . "\n". ' </div>' . "\n";

        $calendar_output .= '<div class="weekday">' . "\n";

            // Create week day name list.
            $week_days_name = array();
            for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
                $week_days_name[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
            }

            // Display week names.
            foreach ( $week_days_name as $wd ) {
                $calendar_output .= '<div class="fec-week-name"> ' . esc_attr( $wd ) . ' </div>'. "\n";
            }
    
        $calendar_output .= '</div>'. "\n";

        $calendar_output .= '<div class="month-days">'. "\n";

            // See how much we should empty in the beginning.
            $empty_date = calendar_week_mod( gmdate( 'w', $unixmonth ) - $week_begins );
            if ( 0 != $empty_date ) {
                for ( $pad_day = 1; $pad_day <= $empty_date; ++$pad_day ) {
                    $calendar_output .= '<div class="empty-day day-frame">&nbsp;</div>'. "\n";
                }
            }

            // Get The number of days in the given month.
            $days_in_month = (int) gmdate( 't', $unixmonth );

            for ( $day = 1; $day <= $days_in_month; ++$day ) {

                // Get an integer Unix timestamp of the day. 
                $unixday = mktime( 0, 0, 0, $month_number, $day, $year_number );

                // add today class for active day.
                if ( current_time( 'j' ) == $day && current_time( 'm' ) == $month_number && current_time( 'Y' ) == $year_number ) {
                    $calendar_output .= '<div class="day-frame today">'. "\n";
                } else {
                    $calendar_output .= '<div class="day-frame">'. "\n";
                }

                // Display day number.
                $calendar_output .= '<span class="day-number">' . $day . '</span>'. "\n";
                
                // Checking if have event for this day.
                if( isset( $events_array[$unixday] ) ){
                    $calendar_output .= '<div class="day-events">'. "\n";
                    foreach ($events_array[$unixday] as $vsalue) {
                        $calendar_output .= '<a target="_blank" href="' . $vsalue['link'] . '">' . $vsalue['title'] . '</a>'. "\n";
                    }
                    $calendar_output .= '</div>' . "\n";
                }
                
                $calendar_output .= '</div>'. "\n";
            }

        $calendar_output .= '</div>'. "\n";

        $calendar_output .= '</div>'. "\n";
        ?>
            <div class="wrap">
                <?php echo $calendar_output; ?>
            </div>
        <?php
    }

}