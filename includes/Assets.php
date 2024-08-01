<?php

namespace Fluent_EC;

/**
 * Class used to register and enqueue assets across our plugins.
 * 
 * @class Assets
 */
class Assets {

    /**
	 *  __construct
     * 
     * @return void
     */
    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets'), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 10, 1 );
    }

    public function register_assets( $hook ) {

        // Only admin panel css
        wp_register_style( 'fec-admin', FEC_ASSETS . '/css/fec-admin-style.css', array(), FEC_VERSION, 'all' );

        // Only Frontend css
        wp_register_style( 'fec-frontend', FEC_ASSETS . '/css/fec-frontend-style.css', array(), FEC_VERSION, 'all' );
    }
}