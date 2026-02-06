<?php
/**
 * PHPUnit bootstrap file for RationalCleanup tests.
 *
 * Uses Brain\Monkey to mock WordPress functions.
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Global test state for controlling function behavior
global $rationalcleanup_test_state;
$rationalcleanup_test_state = [
    'is_user_logged_in' => false,
    'is_admin'          => false,
];

// Define WordPress constants before anything else
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 3600 );
}

// Define WordPress functions that are called at plugin load time
// These need to exist BEFORE the plugin file is loaded

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return dirname( $file ) . '/';
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) {
        return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return basename( dirname( $file ) ) . '/' . basename( $file );
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {
        // No-op for testing
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
        // No-op for testing
    }
}

if ( ! function_exists( 'register_uninstall_hook' ) ) {
    function register_uninstall_hook( $file, $callback ) {
        // No-op for testing
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // No-op for testing
        return true;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // No-op for testing
        return true;
    }
}

if ( ! function_exists( 'remove_action' ) ) {
    function remove_action( $hook, $callback, $priority = 10 ) {
        // No-op for testing
        return true;
    }
}

if ( ! function_exists( 'remove_filter' ) ) {
    function remove_filter( $hook, $callback, $priority = 10 ) {
        // No-op for testing
        return true;
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $args = get_object_vars( $args );
        } elseif ( ! is_array( $args ) ) {
            parse_str( $args, $args );
        }
        return array_merge( $defaults, $args );
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $string, $remove_breaks = false ) {
        return strip_tags( $string );
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) {
        return true;
    }
}

if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
    }
}

if ( ! function_exists( 'remove_meta_box' ) ) {
    function remove_meta_box( $id, $screen, $context ) {
        // No-op for testing
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '' ) {
        return 'http://example.com' . $path;
    }
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
    function is_user_logged_in() {
        global $rationalcleanup_test_state;
        return $rationalcleanup_test_state['is_user_logged_in'] ?? false;
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        global $rationalcleanup_test_state;
        return $rationalcleanup_test_state['is_admin'] ?? false;
    }
}

/**
 * Minimal WP_Error mock for testing.
 */
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        private $code;
        private $message;
        private $data;

        public function __construct( $code = '', $message = '', $data = '' ) {
            $this->code    = $code;
            $this->message = $message;
            $this->data    = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message( $code = '' ) {
            return $this->message;
        }

        public function get_error_data( $code = '' ) {
            return $this->data;
        }
    }
}

// Load the plugin file (class definition only, hooks don't fire)
require_once dirname( __DIR__ ) . '/rationalcleanup.php';
