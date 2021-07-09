<?php
/**
 * CSV-Import
 *
 *
 * @package  CSV-Import
 * @author    Cyril Salvi
 * @license   GPL-3.0
 * @link      https://github.com/6real
 * @copyright 2021 Cyril Salvi
 *
 * @wordpress-plugin
 * Plugin Name:       CSV-Import
 * Plugin URI:        https://vision-marketing.fr
 * Description:       Custom WordPress plugins for CSV-Import
 * Version:           1.1.2
 * Author:            6real
 * Author URI:        https://github.com/6real
 * Text Domain:       csv-import
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:       /languages
 */


namespace Arkanite\CSV;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
if (!defined('WP_REACTIVATE_VERSION')) {
    define('WP_REACTIVATE_VERSION', '1.0.2');
}


/**
 * Autoloader
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 *
 *  * @since 1.0.0
 */

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = __NAMESPACE__;

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/includes/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Initialize Plugin, Admin view and Ajax
 *
 * @since 1.0.0
 */
function init()
{
    //
    $import = Plugin::get_instance();
    $import_admin = Admin::get_instance();

    $wpr_ajax = new AjaxPost();
    $wpr_ajax->hooks();
}


add_action('plugins_loaded', 'Arkanite\\CSV\\init');

//Disable Update Check plugin
add_filter( 'site_transient_update_plugins', function ($value){
    if( isset( $value->response['csv-import/csv-import.php'] ) ) {
        unset( $value->response['csv-import/csv-import.php'] );
    }
    return $value;
} );

/**
 * Register activation and deactivation hooks
 */
register_activation_hook(__FILE__, array('Arkanite\\CSV\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Arkanite\\CSV\\Plugin', 'deactivate'));
