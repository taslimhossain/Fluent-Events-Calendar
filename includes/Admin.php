<?php

namespace Fluent_EC;

/**
 * Admin class.
 *
 * Create Menu and form in admin panel.
 * 
 * @class Admin
 */
class Admin {

    /**
     * Initialize the class
     */
    function __construct() {

        $this->dispatch_actions();
        
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions() {

        // Add menu in wp admin panel
        add_action( 'admin_menu', array( $this, 'admin_menu_list' ) );
    }


    /**
     * Register admin menu
     *
     * @return void
     */
    public function admin_menu_list() {
        
        $calendar = new CalendarPage();
        $post_type_name = fec_get_post_type();
        $calendar_slug = 'edit.php?post_type=' . $post_type_name;

        add_submenu_page( $calendar_slug, esc_html__( 'Calendar', 'fluent-events-calendar'), esc_html__('Calendar', 'fluent-events-calendar'), 'edit_posts', "fluent-events-calendar", array( $calendar, 'calendar_output' ), 10 );
    }

}