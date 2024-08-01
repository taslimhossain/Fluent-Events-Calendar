<?php

namespace Fluent_EC;

/**
 * Frontend class.
 *
 * @class Frontend
 */
class Frontend {
    
    /**
	 *  __construct
	 * 
	 * @return void
     */
    function __construct() {
        add_filter( 'template_include', array( $this, 'template_include' ) );
    }


	/**
	 * Filters the path of the current template before including it.
	 * Fluent events calendar looks for theme overrides in /theme/fluent-events-calendar/ by default
	 * @since 1.0.0
	 *
	 * @param string $template The path of the template to include.
	 * @return string The path of the template to include.
	 */
	public function template_include( $template ) {

        // Get event post type name
		$post_type = fec_get_post_type();

        // Checking is it event single page
        if ( is_single() && get_post_type() == $post_type ) {

            // add event frontend css
            wp_enqueue_style( 'fec-frontend' );

            $file_name = "single-{$post_type}.php";

            $find = array( $file_name );
            $find[] = 'fluent-events-calendar/' . $file_name;

            $theme_file = locate_template( $find );

            // if template found in theme folder then load it from theme.
            if( $theme_file ) {
                return $theme_file;
            }

			// If No template found in theme directory then add event date after event content.
			add_filter( 'the_content', array( $this, 'render_fluent_event_date' ) );
        }

		return $template;
	}

    /**
     * Display event date after event details
     *
     * @since 1.0.0
     * 
     * @param $content
     * @return string
     */
    public function render_fluent_event_date( $content ) {

        global $post;
        $event_details = null;

        // Get event date from event meta
        $event_date = get_post_meta( $post->ID, 'fec_event_date', true );

        if( $event_date ) {
            // Format event date from Unix timestamp event meta value.
            $event_format_date =  gmdate( 'M d, Y', $event_date );
            $event_details = '<div class="fluent-events-meta">';
            $event_details .= '<span class="fluent-events-date">' . esc_html( __( 'Event date: ', 'fluent-events-calendar' ) ) . esc_attr( $event_format_date ) . '</span>';
            $event_details .= '</div>';
        }

        return $content . $event_details;
    }

}