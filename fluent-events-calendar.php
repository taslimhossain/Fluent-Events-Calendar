<?php 
/*
 * Plugin Name: Fluent Events Calendar
 * Plugin URI:  http://taslimhossain.com/plugins/fluent-events-calendar/
 * Description: Fluent Events Calendar is a well-made plugin that makes it simple to share your events.
 * Version:     1.0.0
 * Author:      taslim
 * Author URI:  https://taslimhossain.com/
 * Text Domain: fluent-events-calendar
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * A class that defines the main features of the fluent events calendar plugin.
 */
class Fluent_Events_Calendar {
    
    /**
     * The single instance of the class.
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Version of plugin.
     *
     * @var plugin version
     */
    public static $fec_verion = '1.0';

    /**
	 *  __construct
     * 
     * Sets up all the appropriate hooks and actions within our plugin.
     * 
     * @since 1.0.0
	 * @return void
     */
    private function __construct() {

        // all the files for the fluent events calendar plugin.
        require_once __DIR__ . '/vendor/autoload.php';

        // Define constant.
        $this->define_constants();
        
        // initialize the plugin
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
    }

    /**
     * Main fluent events calendar Instance.
     *
     * Ensures only one instance of fluent events calendar is loaded or can be loaded.
     * 
     * @since 1.0.0
     * @return \Fluent_Events_Calendar
     */
    public static function init() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Define the required plugin constants
     *
	 * @since 1.0.0
	 * @return void
     */
    public function define_constants() {

        if ( ! defined( 'FEC_VERSION' ) ) {
            define( 'FEC_VERSION', self::$fec_verion );
        }
        
        if ( ! defined( 'FEC_FILE' ) ) {
            define( 'FEC_FILE', __FILE__ );
        }
        
        if ( ! defined( 'FEC_PATH' ) ) {
            define( 'FEC_PATH', __DIR__ );
        }
        
        if ( ! defined( 'FEC_URL' ) ) {
            define( 'FEC_URL', plugins_url( '', FEC_FILE ) );
        }

        if ( ! defined( 'FEC_ASSETS' ) ) {
            define( 'FEC_ASSETS', FEC_URL . '/assets' );
        }

        if ( ! defined( 'FEC_POST_TYPE' ) ) {
            define( 'FEC_POST_TYPE', 'fluent_events' );
        }
    }

    /**
     * Initialize the plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function init_plugin() {

        // Enqueue scripts.
        new Fluent_EC\Assets();

        //Create initial post type.
        new Fluent_EC\Fluent_Events_Calendar_Post_Types();

        // Check if it's admin.
        if ( is_admin() ) {
            new Fluent_EC\Admin();
        } else {
            new Fluent_EC\Frontend();
        }
    }
}

/**
 * Main instance of Fluent Events Calendar.
 *
 * @since 1.0.0
 * @return Fluent_Events_Calendar
 */
if ( ! function_exists( 'fluent_events_calendar' ) ) {
    function fluent_events_calendar() {
        return Fluent_Events_Calendar::init();
    }
}

// Run the plugin.
fluent_events_calendar();