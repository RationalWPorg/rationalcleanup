<?php
/**
 * Tests for RationalCleanup options handling.
 */

namespace RationalCleanup\Tests\Unit;

use RationalCleanup\Tests\TestCase;

class OptionsTest extends TestCase {

    /**
     * Test that get_defaults returns all expected options.
     */
    public function test_get_defaults_returns_all_options(): void {
        $instance = $this->createInstance();
        $defaults = $instance->get_defaults();

        // Verify all 24 options are present
        $expected_options = [
            // Head Tags
            'remove_generator',
            'remove_wlw_manifest',
            'remove_rsd_link',
            'remove_shortlink',
            'remove_rest_api_link',
            'remove_feed_links',
            // Frontend
            'remove_emoji',
            'remove_jquery_migrate',
            'remove_block_library_css',
            'remove_global_styles',
            // Security
            'disable_xmlrpc',
            'block_user_enumeration',
            'obfuscate_login_errors',
            // Performance
            'disable_self_pingbacks',
            'throttle_heartbeat',
            'extend_autosave',
            // Features
            'disable_comments',
            'disable_block_editor',
            'disable_rest_api_public',
            // Admin
            'disable_dashboard_primary',
            'disable_dashboard_quick_press',
            'disable_dashboard_right_now',
            'disable_dashboard_activity',
            'disable_dashboard_site_health',
        ];

        foreach ( $expected_options as $option ) {
            $this->assertArrayHasKey( $option, $defaults, "Missing option: $option" );
        }

        $this->assertCount( 24, $defaults );
    }

    /**
     * Test that security-focused options are enabled by default.
     */
    public function test_security_options_enabled_by_default(): void {
        $instance = $this->createInstance();
        $defaults = $instance->get_defaults();

        // These security options should be true by default
        $this->assertTrue( $defaults['disable_xmlrpc'] );
        $this->assertTrue( $defaults['block_user_enumeration'] );
        $this->assertTrue( $defaults['obfuscate_login_errors'] );
    }

    /**
     * Test that cleanup options are enabled by default.
     */
    public function test_cleanup_options_enabled_by_default(): void {
        $instance = $this->createInstance();
        $defaults = $instance->get_defaults();

        // These cleanup options should be true by default
        $this->assertTrue( $defaults['remove_generator'] );
        $this->assertTrue( $defaults['remove_wlw_manifest'] );
        $this->assertTrue( $defaults['remove_rsd_link'] );
        $this->assertTrue( $defaults['remove_shortlink'] );
        $this->assertTrue( $defaults['remove_rest_api_link'] );
        $this->assertTrue( $defaults['remove_emoji'] );
        $this->assertTrue( $defaults['remove_jquery_migrate'] );
        $this->assertTrue( $defaults['disable_self_pingbacks'] );
    }

    /**
     * Test that potentially breaking options are disabled by default.
     */
    public function test_potentially_breaking_options_disabled_by_default(): void {
        $instance = $this->createInstance();
        $defaults = $instance->get_defaults();

        // These could break functionality, so should be false by default
        $this->assertFalse( $defaults['remove_feed_links'] );
        $this->assertFalse( $defaults['remove_block_library_css'] );
        $this->assertFalse( $defaults['remove_global_styles'] );
        $this->assertFalse( $defaults['disable_comments'] );
        $this->assertFalse( $defaults['disable_block_editor'] );
        $this->assertFalse( $defaults['disable_rest_api_public'] );
        $this->assertFalse( $defaults['throttle_heartbeat'] );
        $this->assertFalse( $defaults['extend_autosave'] );
    }

    /**
     * Test that admin dashboard options are disabled by default.
     */
    public function test_admin_dashboard_options_disabled_by_default(): void {
        $instance = $this->createInstance();
        $defaults = $instance->get_defaults();

        $this->assertFalse( $defaults['disable_dashboard_primary'] );
        $this->assertFalse( $defaults['disable_dashboard_quick_press'] );
        $this->assertFalse( $defaults['disable_dashboard_right_now'] );
        $this->assertFalse( $defaults['disable_dashboard_activity'] );
        $this->assertFalse( $defaults['disable_dashboard_site_health'] );
    }

    /**
     * Test sanitize_options converts checkbox values to booleans.
     */
    public function test_sanitize_options_converts_to_booleans(): void {
        $instance = $this->createInstance();

        // Simulate form input with '1' for checked boxes
        $input = [
            'remove_generator'     => '1',
            'remove_wlw_manifest'  => '1',
            'remove_rsd_link'      => '',
            'remove_shortlink'     => '0',
        ];

        $sanitized = $instance->sanitize_options( $input );

        $this->assertTrue( $sanitized['remove_generator'] );
        $this->assertTrue( $sanitized['remove_wlw_manifest'] );
        $this->assertFalse( $sanitized['remove_rsd_link'] );
        $this->assertFalse( $sanitized['remove_shortlink'] );
    }

    /**
     * Test sanitize_options handles missing keys gracefully.
     */
    public function test_sanitize_options_handles_missing_keys(): void {
        $instance = $this->createInstance();

        // Empty input - all should be false
        $sanitized = $instance->sanitize_options( [] );

        $this->assertFalse( $sanitized['remove_generator'] );
        $this->assertFalse( $sanitized['disable_xmlrpc'] );
        $this->assertFalse( $sanitized['disable_comments'] );
    }

    /**
     * Test sanitize_options ignores unknown keys.
     */
    public function test_sanitize_options_ignores_unknown_keys(): void {
        $instance = $this->createInstance();

        $input = [
            'remove_generator'  => '1',
            'unknown_option'    => '1',
            'another_unknown'   => 'some_value',
        ];

        $sanitized = $instance->sanitize_options( $input );

        $this->assertArrayNotHasKey( 'unknown_option', $sanitized );
        $this->assertArrayNotHasKey( 'another_unknown', $sanitized );
        $this->assertTrue( $sanitized['remove_generator'] );
    }
}
