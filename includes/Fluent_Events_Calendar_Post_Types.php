<?php

namespace Fluent_EC;

class Fluent_Events_Calendar_Post_Types {
    
    /**
     * Name of event post type.
     *
     * @var String
     */
    public $post_type_name;

    function __construct() {

        // Get the name of event post type.
        $this->post_type_name = fec_get_post_type();

        // Register event post type.
        add_action( 'init', array( $this, 'register_post_types' ) );

        // Add date field in event post type.
        add_action( "add_meta_boxes_{$this->post_type_name}", array( $this, 'add_meta_boxes' ) );

        // Save event post meta value.
        add_action( 'save_post', array( $this, 'save_post' ) );

        // Add event date columns to event list.
        add_filter( "manage_edit-{$this->post_type_name}_columns", array( $this, 'event_columns' ), 10, 1 );
        
        // Add event date to event date column.
        add_action( "manage_{$this->post_type_name}_posts_custom_column", array( $this, 'event_custom_column' ), 10, 2 );

        // Sortable event date column.
		add_filter( "manage_edit-{$this->post_type_name}_sortable_columns", array( $this, 'event_date_sortable_column' ), 10, 1 );

		//Sort events by event date.
		add_action( 'pre_get_posts', array( $this, 'sort_events_by_event_date_value' ), 10, 1 );
    }

	/**
	 * Register core post types.
     *
     * @return void
	 */
    public function register_post_types() {

        if ( ! is_blog_installed() || post_type_exists( $this->post_type_name ) ) {
			return;
		}

		register_post_type(
            $this->post_type_name, array(
                'labels'              => array(
                    'name'                  => __( 'Events', 'fluent-events-calendar' ),
                    'singular_name'         => __( 'Event', 'fluent-events-calendar' ),
                    'add_new'               => __( 'Add New Event', 'fluent-events-calendar' ),
                    'add_new_item'          => __( 'Add New Event', 'fluent-events-calendar' ),
                    'edit'                  => __( 'Edit', 'fluent-events-calendar' ),
                    'edit_item'             => __( 'Edit Event', 'fluent-events-calendar' ),
                    'new_item'              => __( 'New Event', 'fluent-events-calendar' ),
                    'view'                  => __( 'View Event', 'fluent-events-calendar' ),
                    'view_item'             => __( 'View Event', 'fluent-events-calendar' ),
                    'all_items'             => __( 'All Events', 'fluent-events-calendar' ),
                    'not_found'             => __( 'No Events found', 'fluent-events-calendar' ),
                    'not_found_in_trash'    => __( 'No Events found in trash', 'fluent-events-calendar' ),
                    'featured_image'        => __( 'Event Image', 'fluent-events-calendar' ),
                    'set_featured_image'    => __( 'Set event image', 'fluent-events-calendar' ),
                    'remove_featured_image' => __( 'Remove event image', 'fluent-events-calendar' ),
                    'use_featured_image'    => __( 'Use as event image', 'fluent-events-calendar' ),
                    'menu_name'             => __( 'Fluent Events', 'fluent-events-calendar'  ),
                    'search_items'          => __( 'Search Event', 'fluent-events-calendar'  ),
                ),
                'description'        => __( 'Here you can add new events.', 'fluent-events-calendar' ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_nav_menus'  => true,
                'show_in_menu'       => true,
                'has_archive'        => false,
                'map_meta_cap'        => true,
                'exclude_from_search' => false,
                'hierarchical'        => false,
                'query_var'           => true,
                'menu_icon'           => 'dashicons-calendar-alt',
                'supports'            => array( 'title', 'editor', 'thumbnail', 'publicize' ),
                'rewrite'             => array( 'slug' => 'fluent_events' ),
                //'show_in_rest'        => true
            )
		);

        flush_rewrite_rules();
    }

    /**
     * Register custom meta field
     * 
     * @return void
     */
    public function add_meta_boxes() {
        add_meta_box( 'fluent-events-calendar-meta-box', __( 'Event Details', 'fluent-events-calendar' ), array( $this, 'render_event_meta_box' ), $this->post_type_name, 'advanced' );
    }

    /**
     * Displays the event metabox.
     * 
     * @return void
     */
    public function render_event_meta_box() {
        global $post;

        // Get event date by event id
        $event_date = get_post_meta( $post->ID, 'fec_event_date', true );
        
        // Format event date from Unix timestamp event meta value.
        if( $event_date ) {
            $event_format_date =  gmdate( 'Y-m-d', $event_date );
        } else {
            $event_format_date =  date_i18n( 'Y-m-d' );
        }
        
        wp_nonce_field( 'fluent_event_calendar_meta_box_data', 'fluent_event_calendar_meta_box_nonce' );

        ?>
        <table class="fluent-event-calendar-meta-box-table">
            <tr>
				<th><?php esc_html_e( 'Event Date', 'fluent-events-calendar' ); ?></th>
				<td><input id="fec_event_date" name="fec_event_date" class="fec_date" type="date" value="<?php echo esc_attr( $event_format_date ); ?>" /></td>
        </table>
        <?php
    }

	/**
	 * Save the value of the event metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The current post ID.
	 */
    public function save_post( $post_id ) {

        //Check if it was sent from the target form.
		if ( ! isset( $_POST['fluent_event_calendar_meta_box_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['fluent_event_calendar_meta_box_nonce'], 'fluent_event_calendar_meta_box_data' ) ) { 
            // phpcs:ignore
			return $post_id;
		}

        // Check if it is an autosave routine. If it was, don't submit the form (do nothing).
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

        $event_date   = isset( $_POST['fec_event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['fec_event_date'] ) ) : null;

        if( $event_date ) {
            update_post_meta( $post_id, 'fec_event_date', strtotime( $event_date ) );
        }
    }

	/**
	 * Add event date columns to event list
	 *
	 * @param array $columns
	 * @since 1.0.0
	 * @return array
	 */
	public function event_columns( $columns ) {

        if( isset($columns['date']) ){
            unset( $columns['date'] );
        }

		// Add new column.
		$columns['event_date'] = __( 'Event date', 'fluent-events-calendar' );
		$columns['date'] = __( 'Event Published', 'fluent-events-calendar' );

		return $columns;
	}

	/**
	 * Show event date in event list event date column.
	 *
	 * @param string $column
	 * @param int    $post_id
	 * @since 1.0.0
	 * @return void
	 */
	public function event_custom_column( $column, $post_id ) {
        if ( 'event_date' === $column ) {
            $event_format_date = '-';
            // get event date.
            $event_date = get_post_meta( $post_id, 'fec_event_date', true );
            if( $event_date ) {
                $event_format_date =  gmdate( 'd M Y', $event_date );
            }
            
            echo esc_attr( $event_format_date );
        }
    }

	/**
	 * Sortable event column
	 *
	 * @param array $columns
	 * @since 1.0.0
	 * @return array
	 */
	public function event_date_sortable_column( $columns ) {
		// event date column args for shorting.
		$columns['event_date'] = 'event_date';

		return $columns;
	}

	/**
	 * Sort events by event date
	 *
	 * @param WP_Query $query
	 * @since 1.0.0
	 * @return void
	 */
	public function sort_events_by_event_date_value( $query ) {

		$orderby = $query->get( 'orderby' );

		// Sort events by event date.
		if ( 'event_date' === $orderby ) {
			$query->set( 'meta_key', 'fec_event_date' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

}