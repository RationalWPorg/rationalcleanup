<?php
/**
 * Tests for RationalCleanup filter callbacks.
 */

namespace RationalCleanup\Tests\Unit;

use RationalCleanup\Tests\TestCase;

class FiltersTest extends TestCase {

    /**
     * Test remove_emoji_dns_prefetch removes emoji URLs.
     */
    public function test_remove_emoji_dns_prefetch_removes_emoji_urls(): void {
        $instance = $this->createInstance();

        $urls = [
            'https://fonts.googleapis.com',
            'https://s.w.org/images/core/emoji/14.0.0/72x72/',
            'https://example.com',
        ];

        $result = $instance->remove_emoji_dns_prefetch( $urls, 'dns-prefetch' );

        $this->assertContains( 'https://fonts.googleapis.com', $result );
        $this->assertContains( 'https://example.com', $result );
        $this->assertNotContains( 'https://s.w.org/images/core/emoji/14.0.0/72x72/', $result );
    }

    /**
     * Test remove_emoji_dns_prefetch only affects dns-prefetch relation type.
     */
    public function test_remove_emoji_dns_prefetch_ignores_other_relation_types(): void {
        $instance = $this->createInstance();

        $urls = [
            'https://fonts.googleapis.com',
            'https://s.w.org/images/core/emoji/14.0.0/72x72/',
        ];

        // Should not filter for preconnect
        $result = $instance->remove_emoji_dns_prefetch( $urls, 'preconnect' );

        $this->assertCount( 2, $result );
        $this->assertContains( 'https://s.w.org/images/core/emoji/14.0.0/72x72/', $result );
    }

    /**
     * Test remove_emoji_dns_prefetch handles empty array.
     */
    public function test_remove_emoji_dns_prefetch_handles_empty_array(): void {
        $instance = $this->createInstance();

        $result = $instance->remove_emoji_dns_prefetch( [], 'dns-prefetch' );

        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    /**
     * Test remove_emoji_tinymce removes wpemoji plugin.
     */
    public function test_remove_emoji_tinymce_removes_wpemoji(): void {
        $instance = $this->createInstance();

        $plugins = [ 'lists', 'wpemoji', 'wplink', 'media' ];

        $result = $instance->remove_emoji_tinymce( $plugins );

        $this->assertContains( 'lists', $result );
        $this->assertContains( 'wplink', $result );
        $this->assertContains( 'media', $result );
        $this->assertNotContains( 'wpemoji', $result );
    }

    /**
     * Test remove_emoji_tinymce handles array without wpemoji.
     */
    public function test_remove_emoji_tinymce_handles_no_wpemoji(): void {
        $instance = $this->createInstance();

        $plugins = [ 'lists', 'wplink', 'media' ];

        $result = $instance->remove_emoji_tinymce( $plugins );

        $this->assertCount( 3, $result );
    }

    /**
     * Test remove_emoji_tinymce handles non-array input.
     */
    public function test_remove_emoji_tinymce_handles_non_array(): void {
        $instance = $this->createInstance();

        $result = $instance->remove_emoji_tinymce( null );

        $this->assertNull( $result );
    }

    /**
     * Test remove_pingback_header removes X-Pingback header.
     */
    public function test_remove_pingback_header_removes_header(): void {
        $instance = $this->createInstance();

        $headers = [
            'X-Pingback'   => 'http://example.com/xmlrpc.php',
            'Content-Type' => 'text/html',
        ];

        $result = $instance->remove_pingback_header( $headers );

        $this->assertArrayNotHasKey( 'X-Pingback', $result );
        $this->assertArrayHasKey( 'Content-Type', $result );
    }

    /**
     * Test disable_xmlrpc_methods returns empty array.
     */
    public function test_disable_xmlrpc_methods_returns_empty(): void {
        $instance = $this->createInstance();

        $methods = [
            'pingback.ping' => 'this:pingback_ping',
            'system.multicall' => 'this:multicall',
        ];

        $result = $instance->disable_xmlrpc_methods( $methods );

        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    /**
     * Test throttle_heartbeat sets interval to 60 seconds.
     */
    public function test_throttle_heartbeat_sets_interval(): void {
        $instance = $this->createInstance();

        $settings = [ 'interval' => 15 ];

        $result = $instance->throttle_heartbeat( $settings );

        $this->assertEquals( 60, $result['interval'] );
    }

    /**
     * Test extend_autosave_interval returns 120 seconds.
     */
    public function test_extend_autosave_interval_returns_120(): void {
        $instance = $this->createInstance();

        $result = $instance->extend_autosave_interval( 60 );

        $this->assertEquals( 120, $result );
    }
}
