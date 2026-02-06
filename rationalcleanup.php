<?php
/**
 * Plugin Name: RationalCleanup
 * Plugin URI: https://rationalwp.com/plugins/cleanup/
 * Description: Clean up legacy WordPress bloat, improve security, and optimize performance. All features are toggleable with opinionated defaults.
 * Version: 1.1.0
 * Author: RationalWP
 * Author URI: https://rationalwp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rationalcleanup
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RATIONALCLEANUP_VERSION', '1.1.0' );
define( 'RATIONALCLEANUP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RATIONALCLEANUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RATIONALCLEANUP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load shared RationalWP admin menu.
require_once RATIONALCLEANUP_PLUGIN_DIR . 'includes/rationalwp-admin-menu.php';

class RationalCleanup {

	private static $instance = null;
	private $options;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->options = get_option( 'rationalcleanup_options', array() );
		$this->options = wp_parse_args( $this->options, $this->get_defaults() );

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$this->apply_cleanups();
	}

	public function get_defaults() {
		return array(
			// Head Tags (Phase 1)
			'remove_generator'        => true,
			'remove_wlw_manifest'     => true,
			'remove_rsd_link'         => true,
			'remove_shortlink'        => true,
			'remove_rest_api_link'    => true,
			'remove_feed_links'       => false,

			// Frontend (Phase 2)
			'remove_emoji'            => true,
			'remove_jquery_migrate'   => true,
			'remove_block_library_css' => false,
			'remove_global_styles'    => false,

			// Security (Phase 3)
			'disable_xmlrpc'          => true,
			'block_user_enumeration'  => true,
			'obfuscate_login_errors'  => true,

			// Performance (Phase 4)
			'disable_self_pingbacks'  => true,
			'throttle_heartbeat'      => false,
			'extend_autosave'         => false,

			// Features (Phase 5)
			'disable_comments'        => false,
			'disable_block_editor'    => false,
			'disable_rest_api_public' => false,

			// Admin (Phase 6)
			'disable_dashboard_primary'     => false,
			'disable_dashboard_quick_press' => false,
			'disable_dashboard_right_now'   => false,
			'disable_dashboard_activity'    => false,
			'disable_dashboard_site_health' => false,

			// Third-Party Dashboard Widgets
			'disabled_third_party_widgets' => array(),
		);
	}

	private function apply_cleanups() {
		// Head Tags
		if ( $this->is_enabled( 'remove_generator' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		if ( $this->is_enabled( 'remove_wlw_manifest' ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( $this->is_enabled( 'remove_rsd_link' ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( $this->is_enabled( 'remove_shortlink' ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		if ( $this->is_enabled( 'remove_rest_api_link' ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
		}

		if ( $this->is_enabled( 'remove_feed_links' ) ) {
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

		// Frontend
		if ( $this->is_enabled( 'remove_emoji' ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			add_filter( 'wp_resource_hints', array( $this, 'remove_emoji_dns_prefetch' ), 10, 2 );
			add_filter( 'tiny_mce_plugins', array( $this, 'remove_emoji_tinymce' ) );
			add_filter( 'emoji_svg_url', '__return_false' );
		}

		if ( $this->is_enabled( 'remove_jquery_migrate' ) ) {
			add_action( 'wp_default_scripts', array( $this, 'remove_jquery_migrate' ) );
		}

		if ( $this->is_enabled( 'remove_block_library_css' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_library_css' ), 100 );
		}

		if ( $this->is_enabled( 'remove_global_styles' ) ) {
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
			remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		}

		// Security
		if ( $this->is_enabled( 'disable_xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array( $this, 'remove_pingback_header' ) );
			add_filter( 'xmlrpc_methods', array( $this, 'disable_xmlrpc_methods' ) );
		}

		if ( $this->is_enabled( 'block_user_enumeration' ) ) {
			add_filter( 'redirect_canonical', array( $this, 'block_author_enumeration' ), 10, 2 );
			add_filter( 'rest_endpoints', array( $this, 'block_rest_users_endpoint' ) );
		}

		if ( $this->is_enabled( 'obfuscate_login_errors' ) ) {
			add_filter( 'login_errors', array( $this, 'obfuscate_login_errors' ) );
		}

		// Performance
		if ( $this->is_enabled( 'disable_self_pingbacks' ) ) {
			add_action( 'pre_ping', array( $this, 'disable_self_pingbacks' ) );
		}

		if ( $this->is_enabled( 'throttle_heartbeat' ) ) {
			add_filter( 'heartbeat_settings', array( $this, 'throttle_heartbeat' ) );
		}

		if ( $this->is_enabled( 'extend_autosave' ) ) {
			add_filter( 'wp_autosave_interval', array( $this, 'extend_autosave_interval' ) );
		}

		// Features
		if ( $this->is_enabled( 'disable_comments' ) ) {
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );
			add_filter( 'comments_array', '__return_empty_array', 10, 2 );
			add_action( 'admin_init', array( $this, 'disable_comments_admin_init' ) );
			add_action( 'admin_menu', array( $this, 'disable_comments_admin_menu' ), 999 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'disable_comments_admin_bar' ) );
		}

		if ( $this->is_enabled( 'disable_block_editor' ) ) {
			add_filter( 'use_block_editor_for_post_type', '__return_false', 10, 2 );
			add_filter( 'use_block_editor_for_post', '__return_false', 10, 2 );
		}

		if ( $this->is_enabled( 'disable_rest_api_public' ) ) {
			add_filter( 'rest_authentication_errors', array( $this, 'disable_rest_api_public' ) );
		}

		// Admin
		if (
			$this->is_enabled( 'disable_dashboard_primary' ) ||
			$this->is_enabled( 'disable_dashboard_quick_press' ) ||
			$this->is_enabled( 'disable_dashboard_right_now' ) ||
			$this->is_enabled( 'disable_dashboard_activity' ) ||
			$this->is_enabled( 'disable_dashboard_site_health' )
		) {
			add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		}

		// Third-party dashboard widget scanning and removal (always runs).
		add_action( 'wp_dashboard_setup', array( $this, 'scan_and_remove_third_party_widgets' ), 999 );
	}

	private function is_enabled( $option ) {
		return isset( $this->options[ $option ] ) && $this->options[ $option ];
	}

	/**
	 * Get the list of core WordPress dashboard widget IDs.
	 *
	 * @since 1.1.0
	 *
	 * @return array List of core widget IDs.
	 */
	private function get_core_widget_ids() {
		return array(
			'dashboard_primary',
			'dashboard_quick_press',
			'dashboard_right_now',
			'dashboard_activity',
			'dashboard_site_health',
			'dashboard_browser_nag',
			'dashboard_php_nag',
			'network_dashboard_right_now',
			'dashboard_incoming_links',
			'dashboard_plugins',
			'dashboard_secondary',
			'dashboard_recent_drafts',
			'dashboard_recent_comments',
		);
	}

	/**
	 * Get the list of disabled third-party widget IDs.
	 *
	 * @since 1.1.0
	 *
	 * @return array List of disabled third-party widget IDs.
	 */
	private function get_disabled_third_party_widgets() {
		if ( isset( $this->options['disabled_third_party_widgets'] ) && is_array( $this->options['disabled_third_party_widgets'] ) ) {
			return $this->options['disabled_third_party_widgets'];
		}
		return array();
	}

	/**
	 * Scan for third-party dashboard widgets and remove disabled ones.
	 *
	 * Hooked on wp_dashboard_setup at priority 999 to run after all plugins
	 * have registered their widgets.
	 *
	 * @since 1.1.0
	 */
	public function scan_and_remove_third_party_widgets() {
		global $wp_meta_boxes;

		if ( empty( $wp_meta_boxes['dashboard'] ) || ! is_array( $wp_meta_boxes['dashboard'] ) ) {
			return;
		}

		$core_ids    = $this->get_core_widget_ids();
		$found       = array();
		$now         = time();
		$contexts    = array( 'normal', 'side', 'column3', 'column4' );

		foreach ( $wp_meta_boxes['dashboard'] as $context => $priorities ) {
			if ( ! is_array( $priorities ) ) {
				continue;
			}
			foreach ( $priorities as $priority => $boxes ) {
				if ( ! is_array( $boxes ) ) {
					continue;
				}
				foreach ( $boxes as $id => $box ) {
					if ( false === $box || ! is_array( $box ) ) {
						continue;
					}
					if ( in_array( $id, $core_ids, true ) ) {
						continue;
					}
					$title = isset( $box['title'] ) ? wp_strip_all_tags( $box['title'] ) : $id;
					$found[ $id ] = array(
						'title'     => $title,
						'last_seen' => $now,
					);
				}
			}
		}

		// Merge with existing registry to preserve stale entries.
		$existing = get_option( 'rationalcleanup_third_party_widgets', array() );
		$previous_widgets = isset( $existing['widgets'] ) && is_array( $existing['widgets'] ) ? $existing['widgets'] : array();

		// Keep previous entries, update with newly found ones.
		$merged = $previous_widgets;
		foreach ( $found as $id => $data ) {
			$merged[ $id ] = $data;
		}

		$registry = array(
			'last_scan' => $now,
			'widgets'   => $merged,
		);

		update_option( 'rationalcleanup_third_party_widgets', $registry );

		// Remove disabled third-party widgets.
		$disabled = $this->get_disabled_third_party_widgets();
		foreach ( $disabled as $widget_id ) {
			foreach ( $contexts as $context ) {
				remove_meta_box( $widget_id, 'dashboard', $context );
			}
		}
	}

	public function remove_emoji_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_filter( $urls, function( $url ) {
				return false === strpos( $url, 'https://s.w.org/images/core/emoji/' );
			} );
		}
		return $urls;
	}

	public function remove_emoji_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}
		return $plugins;
	}

	public function remove_jquery_migrate( $scripts ) {
		if ( is_admin() ) {
			return;
		}

		if ( isset( $scripts->registered['jquery'] ) ) {
			$jquery = $scripts->registered['jquery'];
			if ( $jquery->deps ) {
				$jquery->deps = array_diff( $jquery->deps, array( 'jquery-migrate' ) );
			}
		}
	}

	public function remove_block_library_css() {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
	}

	public function remove_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	public function disable_xmlrpc_methods( $methods ) {
		return array();
	}

	public function block_author_enumeration( $redirect, $request ) {
		if ( preg_match( '/\?author=(\d+)/i', $request ) ) {
			return false;
		}
		return $redirect;
	}

	public function block_rest_users_endpoint( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			if ( isset( $endpoints['/wp/v2/users'] ) ) {
				unset( $endpoints['/wp/v2/users'] );
			}
			if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
				unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
			}
		}
		return $endpoints;
	}

	public function obfuscate_login_errors( $error ) {
		return __( 'Invalid username or password.', 'rationalcleanup' );
	}

	public function disable_self_pingbacks( &$links ) {
		$home_url = home_url();
		foreach ( $links as $key => $link ) {
			if ( 0 === strpos( $link, $home_url ) ) {
				unset( $links[ $key ] );
			}
		}
	}

	public function throttle_heartbeat( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}

	public function extend_autosave_interval( $interval ) {
		return 120;
	}

	public function disable_comments_admin_init() {
		// Remove comment support from all post types
		$post_types = get_post_types( array(), 'names' );
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	public function disable_comments_admin_menu() {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}

	public function disable_comments_admin_bar() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}

	public function disable_rest_api_public( $result ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You are not currently logged in.', 'rationalcleanup' ),
				array( 'status' => 401 )
			);
		}
		return $result;
	}

	public function remove_dashboard_widgets() {
		if ( $this->is_enabled( 'disable_dashboard_primary' ) ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		}

		if ( $this->is_enabled( 'disable_dashboard_quick_press' ) ) {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}

		if ( $this->is_enabled( 'disable_dashboard_right_now' ) ) {
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		}

		if ( $this->is_enabled( 'disable_dashboard_activity' ) ) {
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		}

		if ( $this->is_enabled( 'disable_dashboard_site_health' ) ) {
			remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		}
	}

	public function add_settings_page() {
		add_submenu_page(
			'rationalwp',
			__( 'Cleanup', 'rationalcleanup' ),
			__( 'Cleanup', 'rationalcleanup' ),
			'manage_options',
			'rationalcleanup',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'rationalcleanup_options_group',
			'rationalcleanup_options',
			array( $this, 'sanitize_options' )
		);

		// Head Tags Section
		add_settings_section(
			'rationalcleanup_head_tags',
			__( 'Head Tags', 'rationalcleanup' ),
			array( $this, 'render_section_head_tags' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'remove_generator', __( 'Remove generator meta tag', 'rationalcleanup' ), 'rationalcleanup_head_tags' );
		$this->add_toggle_field( 'remove_wlw_manifest', __( 'Remove WLW manifest link', 'rationalcleanup' ), 'rationalcleanup_head_tags' );
		$this->add_toggle_field( 'remove_rsd_link', __( 'Remove RSD link', 'rationalcleanup' ), 'rationalcleanup_head_tags' );
		$this->add_toggle_field( 'remove_shortlink', __( 'Remove shortlink', 'rationalcleanup' ), 'rationalcleanup_head_tags' );
		$this->add_toggle_field( 'remove_rest_api_link', __( 'Remove REST API link', 'rationalcleanup' ), 'rationalcleanup_head_tags' );
		$this->add_toggle_field( 'remove_feed_links', __( 'Remove RSS feed links', 'rationalcleanup' ), 'rationalcleanup_head_tags' );

		// Frontend Section
		add_settings_section(
			'rationalcleanup_frontend',
			__( 'Frontend', 'rationalcleanup' ),
			array( $this, 'render_section_frontend' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'remove_emoji', __( 'Remove emoji scripts', 'rationalcleanup' ), 'rationalcleanup_frontend' );
		$this->add_toggle_field( 'remove_jquery_migrate', __( 'Remove jQuery Migrate', 'rationalcleanup' ), 'rationalcleanup_frontend' );
		$this->add_toggle_field( 'remove_block_library_css', __( 'Remove block library CSS', 'rationalcleanup' ), 'rationalcleanup_frontend' );
		$this->add_toggle_field( 'remove_global_styles', __( 'Remove global styles/SVGs', 'rationalcleanup' ), 'rationalcleanup_frontend' );

		// Security Section
		add_settings_section(
			'rationalcleanup_security',
			__( 'Security', 'rationalcleanup' ),
			array( $this, 'render_section_security' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'disable_xmlrpc', __( 'Disable XML-RPC', 'rationalcleanup' ), 'rationalcleanup_security' );
		$this->add_toggle_field( 'block_user_enumeration', __( 'Prevent user enumeration', 'rationalcleanup' ), 'rationalcleanup_security' );
		$this->add_toggle_field( 'obfuscate_login_errors', __( 'Obfuscate login errors', 'rationalcleanup' ), 'rationalcleanup_security' );

		// Performance Section
		add_settings_section(
			'rationalcleanup_performance',
			__( 'Performance', 'rationalcleanup' ),
			array( $this, 'render_section_performance' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'disable_self_pingbacks', __( 'Disable self-pingbacks', 'rationalcleanup' ), 'rationalcleanup_performance' );
		$this->add_toggle_field( 'throttle_heartbeat', __( 'Throttle Heartbeat API', 'rationalcleanup' ), 'rationalcleanup_performance' );
		$this->add_toggle_field( 'extend_autosave', __( 'Extend autosave interval', 'rationalcleanup' ), 'rationalcleanup_performance' );

		// Features Section
		add_settings_section(
			'rationalcleanup_features',
			__( 'Features', 'rationalcleanup' ),
			array( $this, 'render_section_features' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'disable_comments', __( 'Disable comments', 'rationalcleanup' ), 'rationalcleanup_features' );
		$this->add_toggle_field( 'disable_block_editor', __( 'Disable block editor', 'rationalcleanup' ), 'rationalcleanup_features' );
		$this->add_toggle_field( 'disable_rest_api_public', __( 'Disable REST API for public', 'rationalcleanup' ), 'rationalcleanup_features' );

		// Admin Section
		add_settings_section(
			'rationalcleanup_admin',
			__( 'Admin', 'rationalcleanup' ),
			array( $this, 'render_section_admin' ),
			'rationalcleanup'
		);

		$this->add_toggle_field( 'disable_dashboard_primary', __( 'Remove WordPress Events and News widget', 'rationalcleanup' ), 'rationalcleanup_admin' );
		$this->add_toggle_field( 'disable_dashboard_quick_press', __( 'Remove Quick Draft widget', 'rationalcleanup' ), 'rationalcleanup_admin' );
		$this->add_toggle_field( 'disable_dashboard_right_now', __( 'Remove At a Glance widget', 'rationalcleanup' ), 'rationalcleanup_admin' );
		$this->add_toggle_field( 'disable_dashboard_activity', __( 'Remove Activity widget', 'rationalcleanup' ), 'rationalcleanup_admin' );
		$this->add_toggle_field( 'disable_dashboard_site_health', __( 'Remove Site Health Status widget', 'rationalcleanup' ), 'rationalcleanup_admin' );

		// Third-Party Dashboard Widgets Section.
		$third_party_registry = get_option( 'rationalcleanup_third_party_widgets', array() );
		$third_party_widgets  = isset( $third_party_registry['widgets'] ) && is_array( $third_party_registry['widgets'] ) ? $third_party_registry['widgets'] : array();
		$last_scan            = isset( $third_party_registry['last_scan'] ) ? $third_party_registry['last_scan'] : 0;

		add_settings_section(
			'rationalcleanup_third_party_widgets',
			__( 'Third-Party Dashboard Widgets', 'rationalcleanup' ),
			array( $this, 'render_section_third_party_widgets' ),
			'rationalcleanup'
		);

		if ( ! empty( $third_party_widgets ) ) {
			$disabled = $this->get_disabled_third_party_widgets();

			foreach ( $third_party_widgets as $widget_id => $widget_data ) {
				$label = isset( $widget_data['title'] ) ? $widget_data['title'] : $widget_id;

				// Mark stale widgets that were not seen in the last scan.
				$widget_last_seen = isset( $widget_data['last_seen'] ) ? $widget_data['last_seen'] : 0;
				if ( $last_scan > 0 && $widget_last_seen < $last_scan ) {
					$label .= ' ' . __( '(not currently detected)', 'rationalcleanup' );
				}

				add_settings_field(
					'third_party_' . $widget_id,
					$label,
					array( $this, 'render_third_party_toggle' ),
					'rationalcleanup',
					'rationalcleanup_third_party_widgets',
					array(
						'widget_id' => $widget_id,
						'disabled'  => $disabled,
					)
				);
			}
		}
	}

	private function add_toggle_field( $id, $label, $section ) {
		add_settings_field(
			$id,
			$label,
			array( $this, 'render_toggle_field' ),
			'rationalcleanup',
			$section,
			array(
				'id'    => $id,
				'label' => $label,
			)
		);
	}

	public function sanitize_options( $input ) {
		$defaults  = $this->get_defaults();
		$sanitized = array();

		foreach ( array_keys( $defaults ) as $key ) {
			if ( 'disabled_third_party_widgets' === $key ) {
				continue;
			}
			$sanitized[ $key ] = ! empty( $input[ $key ] );
		}

		// Handle the third-party widgets array.
		if ( isset( $input['disabled_third_party_widgets'] ) && is_array( $input['disabled_third_party_widgets'] ) ) {
			$sanitized['disabled_third_party_widgets'] = array_map( 'sanitize_key', $input['disabled_third_party_widgets'] );
		} else {
			$sanitized['disabled_third_party_widgets'] = array();
		}

		return $sanitized;
	}

	public function render_section_head_tags() {
		echo '<p>' . esc_html__( 'Remove unnecessary meta tags and links from the document head.', 'rationalcleanup' ) . '</p>';
	}

	public function render_section_frontend() {
		echo '<p>' . esc_html__( 'Remove scripts and styles that most sites don\'t need.', 'rationalcleanup' ) . '</p>';
	}

	public function render_section_security() {
		echo '<p>' . esc_html__( 'Harden WordPress against common attack vectors.', 'rationalcleanup' ) . '</p>';
	}

	public function render_section_performance() {
		echo '<p>' . esc_html__( 'Reduce unnecessary WordPress overhead.', 'rationalcleanup' ) . '</p>';
	}

	public function render_section_features() {
		echo '<p>' . esc_html__( 'Disable major WordPress subsystems.', 'rationalcleanup' ) . '</p>';
	}

	public function render_section_admin() {
		echo '<p>' . esc_html__( 'Declutter the WordPress admin dashboard.', 'rationalcleanup' ) . '</p>';
	}

	/**
	 * Render the third-party dashboard widgets section description.
	 *
	 * @since 1.1.0
	 */
	public function render_section_third_party_widgets() {
		$registry = get_option( 'rationalcleanup_third_party_widgets', array() );
		$widgets  = isset( $registry['widgets'] ) && is_array( $registry['widgets'] ) ? $registry['widgets'] : array();

		if ( empty( $widgets ) ) {
			echo '<div class="notice notice-info inline" style="margin: 15px 0; padding: 12px;">';
			echo '<p><strong>' . esc_html__( 'No third-party dashboard widgets detected yet.', 'rationalcleanup' ) . '</strong></p>';
			echo '<p>' . esc_html__( 'Third-party widgets (added by plugins like WooCommerce, Elementor, Jetpack, etc.) can only be detected on the Dashboard page. Visit your', 'rationalcleanup' );
			echo ' <a href="' . esc_url( admin_url( '/' ) ) . '">' . esc_html__( 'Dashboard', 'rationalcleanup' ) . '</a> ';
			echo esc_html__( 'once, then return here to manage them.', 'rationalcleanup' ) . '</p>';
			echo '</div>';
		} else {
			echo '<p>' . esc_html__( 'Disable dashboard widgets added by third-party plugins. Visit the Dashboard to update this list.', 'rationalcleanup' ) . '</p>';
		}
	}

	/**
	 * Render a toggle checkbox for a third-party widget.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args Field arguments containing widget_id and disabled list.
	 */
	public function render_third_party_toggle( $args ) {
		$widget_id = $args['widget_id'];
		$disabled  = $args['disabled'];
		$checked   = in_array( $widget_id, $disabled, true );
		?>
		<label class="rationalcleanup-toggle">
			<input type="checkbox"
				name="rationalcleanup_options[disabled_third_party_widgets][]"
				value="<?php echo esc_attr( $widget_id ); ?>"
				<?php checked( $checked ); ?>>
			<span class="rationalcleanup-toggle-slider"></span>
		</label>
		<?php
	}

	public function render_toggle_field( $args ) {
		$id      = $args['id'];
		$checked = $this->is_enabled( $id );
		?>
		<label class="rationalcleanup-toggle">
			<input type="checkbox"
				name="rationalcleanup_options[<?php echo esc_attr( $id ); ?>]"
				value="1"
				<?php checked( $checked ); ?>>
			<span class="rationalcleanup-toggle-slider"></span>
		</label>
		<?php
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'rationalwp_page_rationalcleanup' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'rationalcleanup-admin',
			RATIONALCLEANUP_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			RATIONALCLEANUP_VERSION
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap rationalcleanup-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'rationalcleanup_options_group' );
				do_settings_sections( 'rationalcleanup' );
				submit_button( __( 'Save Settings', 'rationalcleanup' ) );
				?>
			</form>
		</div>
		<?php
	}
}

register_activation_hook( __FILE__, 'rationalcleanup_activate' );
function rationalcleanup_activate() {
	$instance = RationalCleanup::get_instance();
	$defaults = $instance->get_defaults();

	if ( false === get_option( 'rationalcleanup_options' ) ) {
		add_option( 'rationalcleanup_options', $defaults );
	}
}

register_deactivation_hook( __FILE__, 'rationalcleanup_deactivate' );
function rationalcleanup_deactivate() {
	// Options are preserved on deactivation
}

register_uninstall_hook( __FILE__, 'rationalcleanup_uninstall' );
function rationalcleanup_uninstall() {
	delete_option( 'rationalcleanup_options' );
	delete_option( 'rationalcleanup_third_party_widgets' );
}

add_action( 'plugins_loaded', array( 'RationalCleanup', 'get_instance' ) );
