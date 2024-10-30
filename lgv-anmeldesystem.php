<?php
/**
 * @package lgv-anmeldesystem
 * @version 1.21
 */
/*
Plugin Name: lgv-anmeldesystem
Plugin URI: http://wordpress.org/plugins/lgv-anmeldesystem/
Description: Helps to register for free events
Author: jkalmbach
Version: 1.21
Author URI: http://blog.kalmbach-software.de
*/

//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
 header('HTTP/1.0 403 Forbidden');
 exit('Forbidden');
}

define( 'LGV_AS_FILE', __FILE__ );	               // /path/to/wp-content/plugins/lgvanmeldesystem/lgvanmeldesystem.php
define( 'LGV_AS_PATH', plugin_dir_path(__FILE__) );  // /path/to/wp-content/plugins/lgvanmeldesystem/

require_once LGV_AS_PATH . 'includes/class-lgv-as-util.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-bo.php';
require_once LGV_AS_PATH . 'includes/class-lgv-as-db.php';

require_once LGV_AS_PATH . 'includes/class-lgv-as-frontend.php';
LGV_AS_Frontend::setup();

if (is_admin() ) {
	LGV_AS_DB::setup();
}

require_once LGV_AS_PATH . 'includes/class-lgv-as-backend.php';	
LGV_AS_Backend::setup();

?>
