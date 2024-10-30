<?php

/**
 * Description of class-LGV_AS_Install
 *
 * @authors Jochen Kalmbach
 */

class LGV_AS_Install {

	static function setup() {
		//register_activation_hook( LGV_AS_FILE, 'LGV_AS_Install::install' );
		//register_deactivation_hook( LGV_AS_FILE, 'LGV_AS_Install::deactivate' );
		//register_uninstall_hook( LGV_AS_FILE, 'LGV_AS_Install::uninstall')
	}
	
	static function install() {
		// TODO:
	}
	
	static function deactivate() {
		// TODO: (nothing!?)
	}
	
	static function uninstall() {
		//$option_name = 'plugin_option_name';
		//delete_option( $option_name );

		// delete_site_option( $option_name );  // For site options in multisite

		//drop a custom db table
		//global $wpdb;
		//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mytable" );
	}

}  // end class LGV_AS_Install
