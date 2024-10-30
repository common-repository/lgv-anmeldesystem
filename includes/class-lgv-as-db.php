<?php

/**
 * Description of class-LGV_AS_DB
 *
 * @authors Jochen Kalmbach
 */


class LGV_AS_DB {

	static function getEventTableName() {
		global $wpdb;
		return $wpdb->prefix . "lgvas_event";
	}

	static function getRegistrationTableName() {
		global $wpdb;
		return $wpdb->prefix . "lgvas_registration";
	}

	static function getEventGroupTableName() {
		global $wpdb;
		return $wpdb->prefix . "lgvas_eventgroup";
	}

	static function setup() {
		add_action( 'plugins_loaded', 'LGV_AS_DB::update_db_check' );
		register_activation_hook( LGV_AS_FILE, 'LGV_AS_DB::update_db_check' );
	}
	
	static function admin_notice_updated() {
		echo '<div class="updated">
			<p>LGV-Anmeldesystem: Datenbank aktualisiert.</p> 
			</div>'; 
	}

	const lgvas_db_version = "1.12";
	
	static function update_db_check() {
		global $wpdb;

		$installed_ver = get_option( "lgvas_db_version" );
		if( empty($installed_ver) || $installed_ver != self::lgvas_db_version ) {
	
			//echo "<h1>Create / Update Table-" . $installed_ver . "-" . self::lgvas_db_version . "</h1>";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			$table_name = LGV_AS_DB::getEventTableName(); 
			// TOD: Hier sollte die "id" eigentlich auch "event_id" lauten, dann waeren einige Abfragen einfacher!
			// (siehe 'getAllActiveFromEmail')
			$sql = "CREATE TABLE $table_name (
				id INT NOT NULL AUTO_INCREMENT,
				created_date_time DATETIME NOT NULL,
				title TINYTEXT NOT NULL,
				description TEXT NULL,
				event_state SMALLINT NOT NULL,
				max_registrations INT NULL,
				additional_parameters TEXT NULL,
				UNIQUE KEY id (id)
				);";
			dbDelta( $sql );

			$table_name = LGV_AS_DB::getRegistrationTableName(); 
			$sql = "CREATE TABLE $table_name (
				id INT NOT NULL AUTO_INCREMENT,
				event_id INT NOT NULL,
				created_date_time DATETIME NOT NULL,
				email TINYTEXT NULL,
				modify_password TINYTEXT NULL,
				first_name TINYTEXT NULL,
				last_name TINYTEXT NOT NULL,
				reg_state SMALLINT NOT NULL,
				street TINYTEXT NULL,
				zip_code TINYTEXT NULL,
				city TINYTEXT NULL,
				phone TINYTEXT NULL,
				registrations INT NOT NULL,
				parameters TEXT NULL,
				waiting_list_date_time DATETIME NULL,
				UNIQUE KEY id (id)
				);";
			dbDelta( $sql );
			
			$table_name = LGV_AS_DB::getEventGroupTableName(); 
			$sql = "CREATE TABLE $table_name (
				id INT NOT NULL AUTO_INCREMENT,
				group_key TINYTEXT NULL,
				additional_parameters TEXT NULL,
				UNIQUE KEY id (id)
				);";
			dbDelta( $sql );
			
			
			add_action('admin_notices', 'LGV_AS_DB::admin_notice_updated');

			update_option( "lgvas_db_version", self::lgvas_db_version );
		}
	}
		
	static function uninstall() {
		// TODO:
	}	
}  // end class LGV_AS_DB

require_once LGV_AS_PATH . 'includes/class-lgv-as-db-event.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-db-registration.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-db-eventgroup.php';
